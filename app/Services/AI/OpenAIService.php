<?php

namespace App\Services\AI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\StreamInterface;

class OpenAIService
{
    private Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => config('ai_assistant.base_uri'),
            'timeout'  => config('ai_assistant.timeout'),
        ]);
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('ai_assistant.api_key'),
        ];
    }

    public function createVectorStore(string $name): array
    {
        return $this->json('POST', 'vector_stores', [
            'name' => $name,
        ]);
    }

    public function uploadFile(string $absolutePath, string $originalName, string $purpose = 'assistants'): array
    {
        // Official Files API is multipart/form-data and purpose includes "assistants" and "user_data". :contentReference[oaicite:1]{index=1}
        try {
            $res = $this->http->post('files', [
                'headers'   => $this->headers(),
                'multipart' => [
                    [
                        'name'     => 'purpose',
                        'contents' => $purpose,
                    ],
                    [
                        'name'     => 'file',
                        'contents' => fopen($absolutePath, 'r'),
                        'filename' => $originalName,
                    ],
                ],
            ]);

            return json_decode($res->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $msg = $e->getResponse()?->getBody()?->getContents() ?: $e->getMessage();
            throw new \RuntimeException("OpenAI file upload failed: " . $msg);
        }
    }

    public function attachFileToVectorStore(string $vectorStoreId, string $fileId, array $attributes = []): array
    {
        // Vector store file create: POST /vector_stores/{id}/files :contentReference[oaicite:2]{index=2}
        return $this->json('POST', "vector_stores/{$vectorStoreId}/files", [
            'file_id' => $fileId,
            'attributes' => (object) $attributes,
        ]);
    }

    public function retrieveVectorStoreFile(string $vectorStoreId, string $fileId): array
    {
        return $this->json('GET', "vector_stores/{$vectorStoreId}/files/{$fileId}");
    }

    public function createResponseStream(array $payload): StreamInterface
    {
        // Responses streaming uses SSE; key events include response.output_text.delta and response.completed. :contentReference[oaicite:3]{index=3}
        try {
            $res = $this->http->post('responses', [
                'headers' => array_merge($this->headers(), [
                    'Content-Type' => 'application/json',
                ]),
                'json'   => $payload,
                'stream' => true,
            ]);

            return $res->getBody();
        } catch (RequestException $e) {
            $msg = $e->getResponse()?->getBody()?->getContents() ?: $e->getMessage();
            throw new \RuntimeException("OpenAI response stream failed: " . $msg);
        }
    }

    private function json(string $method, string $uri, array $payload = []): array
    {
        try {
            $options = [
                'headers' => array_merge($this->headers(), [
                    'Content-Type' => 'application/json',
                ]),
            ];

            $methodUpper = strtoupper($method);

            // ✅ IMPORTANT: GET requests must NOT have a JSON body
            if ($methodUpper === 'GET') {
                if (!empty($payload)) {
                    $options['query'] = $payload; // if you ever need query params
                }
            } else {
                if (!empty($payload)) {
                    $options['json'] = $payload;
                }
            }

            $res = $this->http->request($methodUpper, $uri, $options);

            return json_decode($res->getBody()->getContents(), true);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $msg = $e->getResponse()?->getBody()?->getContents() ?: $e->getMessage();
            throw new \RuntimeException("OpenAI request failed ({$uri}): " . $msg);
        }
    }
}