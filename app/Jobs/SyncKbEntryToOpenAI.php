<?php

namespace App\Jobs;

use App\Models\AiKbEntry;
use App\Models\AiKbFile;
use App\Services\AI\OpenAIService;
use App\Services\AI\VectorStoreResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SyncKbEntryToOpenAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $entryId) {}

    public function handle(OpenAIService $openai, VectorStoreResolver $resolver): void
    {
        $entry = AiKbEntry::findOrFail($this->entryId);

        $store = $entry->scope === 'course' && $entry->course_id
            ? $resolver->forCourse((int)$entry->course_id)
            : $resolver->global();

        $content = $this->toMarkdown($entry);
        $filename = "kb_entry_{$entry->id}_" . now()->format('Ymd_His') . ".md";
        $path = "ai_kb/{$filename}";

        Storage::disk('local')->put($path, $content);
        $absolute = Storage::disk('local')->path($path);

        $kbFile = AiKbFile::create([
            'scope' => $entry->scope,
            'course_id' => $entry->course_id,
            'ai_kb_entry_id' => $entry->id,
            'original_name' => $filename,
            'stored_path' => $path,
            'mime' => 'text/markdown',
            'size' => strlen($content),
            'status' => 'pending',
            'created_by' => $entry->updated_by ?: $entry->created_by,
        ]);

        try {
            $uploaded = $openai->uploadFile($absolute, $filename, 'assistants');
            $kbFile->openai_file_id = $uploaded['id'];

            $attach = $openai->attachFileToVectorStore(
                $store->openai_vector_store_id,
                $uploaded['id'],
                [
                    'scope' => $entry->scope,
                    'course_id' => (string)($entry->course_id ?? ''),
                    'kb_entry_id' => (string)$entry->id,
                    'type' => $entry->type,
                ]
            );

            $kbFile->openai_vector_store_id = $store->openai_vector_store_id;
            $kbFile->status = ($attach['status'] ?? 'uploaded');
            $kbFile->save();

            // Optional short polling until indexed (vector store file status becomes completed) :contentReference[oaicite:4]{index=4}
            for ($i=0; $i<8; $i++) {
                $state = $openai->retrieveVectorStoreFile($store->openai_vector_store_id, $uploaded['id']);
                $status = $state['status'] ?? null;
                if ($status === 'completed') {
                    $kbFile->status = 'indexed';
                    $kbFile->save();
                    break;
                }
                sleep(1);
            }

            $store->last_synced_at = now();
            $store->save();

        } catch (\Throwable $e) {
            $kbFile->status = 'failed';
            $kbFile->last_error = $e->getMessage();
            $kbFile->save();
            throw $e;
        }
    }

    private function toMarkdown(AiKbEntry $e): string
    {
        $out = [];
        $out[] = "# {$e->title}";
        $out[] = "";
        $out[] = "- Scope: {$e->scope}" . ($e->course_id ? " (course_id={$e->course_id})" : "");
        $out[] = "- Type: {$e->type}";
        if ($e->keywords) $out[] = "- Keywords: {$e->keywords}";
        $out[] = "";

        if ($e->type === 'qa') {
            $out[] = "## Question";
            $out[] = $e->question ?? '';
            $out[] = "";
            $out[] = "## Answer";
            $out[] = $e->answer ?? '';
        } else {
            $out[] = "## Content";
            $out[] = $e->body ?? '';
        }

        $out[] = "";
        $out[] = "## Strict rule";
        $out[] = "Use only this document to answer. If not relevant, say you don't know.";

        return implode("\n", $out);
    }
}