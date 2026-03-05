<?php

namespace App\Services\AI;

use App\Models\AiVectorStore;

class VectorStoreResolver
{
    public function __construct(private OpenAIService $openai) {}

    public function global(): AiVectorStore
    {
        return $this->firstOrCreate('global', null, 'LMS Global Knowledge');
    }

    public function forCourse(int $courseId): AiVectorStore
    {
        return $this->firstOrCreate('course', $courseId, "Course #{$courseId} Knowledge");
    }

    private function firstOrCreate(string $scope, ?int $courseId, string $name): AiVectorStore
    {
        $store = AiVectorStore::query()
            ->where('scope', $scope)
            ->when($courseId, fn($q) => $q->where('course_id', $courseId), fn($q) => $q->whereNull('course_id'))
            ->first();

        if ($store) return $store;

        $created = $this->openai->createVectorStore($name);

        return AiVectorStore::create([
            'scope' => $scope,
            'course_id' => $courseId,
            'name' => $name,
            'openai_vector_store_id' => $created['id'],
            'status' => $created['status'] ?? null,
        ]);
    }
}