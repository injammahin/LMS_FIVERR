<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DictionaryLookupController extends Controller
{
    public function lookup(Request $request)
    {
        $user = auth()->user();
        $userDivision = Division::find($user->division_id);

        abort_unless($this->isMiddleSchoolDivision($userDivision), 403, 'Dictionary is only available for middle school students.');

        $term = trim((string) $request->query('term', ''));

        if ($term === '') {
            return response()->json([
                'message' => 'No word provided.'
            ], 422);
        }

        if (mb_strlen($term) > 60) {
            return response()->json([
                'message' => 'Selected text is too long.'
            ], 422);
        }

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

    private function isMiddleSchoolDivision(?Division $division): bool
    {
        if (!$division) {
            return false;
        }

        $name = strtolower((string) $division->name);

        if (str_contains($name, 'middle')) {
            return true;
        }

        $levels = Division::orderBy('level')->pluck('level')->values();

        if ($levels->count() < 2) {
            return false;
        }

        $middleLevel = (int) $levels[1];

        return (int) $division->level === $middleLevel;
    }
}