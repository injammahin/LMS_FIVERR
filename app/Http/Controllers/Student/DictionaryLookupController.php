<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DictionaryLookupController extends Controller
{
    public function lookup(Request $request)
    {
        $term = trim((string) $request->query('term', ''));

        if ($term === '') {
            return response()->json([
                'message' => 'No word provided.'
            ], 422);
        }

        // keep it safe and focused for school text usage
        if (mb_strlen($term) > 60) {
            return response()->json([
                'message' => 'Selected text is too long.'
            ], 422);
        }

        // normalize spaces
        $term = preg_replace('/\s+/', ' ', $term);

        $response = Http::timeout(8)
            ->acceptJson()
            ->get('https://api.dictionaryapi.dev/api/v2/entries/en/' . urlencode($term));

        if (!$response->ok()) {
            return response()->json([
                'message' => 'Definition not found.'
            ], 404);
        }

        $data = $response->json();

        if (!is_array($data) || empty($data[0])) {
            return response()->json([
                'message' => 'Definition not found.'
            ], 404);
        }

        $entry = $data[0];

        $phonetic = $entry['phonetic'] ?? null;
        $audio = null;

        foreach (($entry['phonetics'] ?? []) as $phoneticItem) {
            if (!empty($phoneticItem['audio'])) {
                $audio = $phoneticItem['audio'];
                break;
            }
        }

        $partOfSpeech = null;
        $definition = null;
        $example = null;

        foreach (($entry['meanings'] ?? []) as $meaning) {
            if (!$partOfSpeech && !empty($meaning['partOfSpeech'])) {
                $partOfSpeech = $meaning['partOfSpeech'];
            }

            if (!empty($meaning['definitions'][0]['definition'])) {
                $definition = $meaning['definitions'][0]['definition'];
                $example = $meaning['definitions'][0]['example'] ?? null;
                break;
            }
        }

        if (!$definition) {
            return response()->json([
                'message' => 'Definition not found.'
            ], 404);
        }

        return response()->json([
            'word' => $entry['word'] ?? $term,
            'phonetic' => $phonetic,
            'audio' => $audio,
            'part_of_speech' => $partOfSpeech,
            'definition' => $definition,
            'example' => $example,
        ]);
    }
}