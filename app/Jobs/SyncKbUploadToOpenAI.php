<?php

namespace App\Jobs;

use App\Models\AiKbFile;
use App\Services\AI\OpenAIService;
use App\Services\AI\VectorStoreResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SyncKbUploadToOpenAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $kbFileId) {}

    public function handle(OpenAIService $openai, VectorStoreResolver $resolver): void
    {
        $kbFile = AiKbFile::findOrFail($this->kbFileId);

        $store = $kbFile->scope === 'course' && $kbFile->course_id
            ? $resolver->forCourse((int)$kbFile->course_id)
            : $resolver->global();

        $absolute = Storage::disk('local')->path($kbFile->stored_path);

        try {
            $uploaded = $openai->uploadFile($absolute, $kbFile->original_name, 'assistants');

            $kbFile->openai_file_id = $uploaded['id'];

            $attach = $openai->attachFileToVectorStore(
                $store->openai_vector_store_id,
                $uploaded['id'],
                [
                    'scope' => $kbFile->scope,
                    'course_id' => (string)($kbFile->course_id ?? ''),
                    'kind' => 'upload',
                ]
            );

            $kbFile->openai_vector_store_id = $store->openai_vector_store_id;
            $kbFile->status = ($attach['status'] ?? 'uploaded');
            $kbFile->save();

            for ($i=0; $i<10; $i++) {
                $state = $openai->retrieveVectorStoreFile($store->openai_vector_store_id, $uploaded['id']);
                if (($state['status'] ?? null) === 'completed') {
                    $kbFile->status = 'indexed';
                    $kbFile->save();
                    break;
                }
                sleep(1);
            }

        } catch (\Throwable $e) {
            $kbFile->status = 'failed';
            $kbFile->last_error = $e->getMessage();
            $kbFile->save();
            throw $e;
        }
    }
}