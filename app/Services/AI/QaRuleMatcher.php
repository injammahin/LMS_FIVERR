<?php

namespace App\Services\AI;

use App\Models\AiKbEntry;
use Illuminate\Support\Str;

class QaRuleMatcher
{
    public function match(string $question, array $scopes): ?AiKbEntry
    {
        $q = Str::lower(trim($question));

        $candidates = AiKbEntry::query()
            ->where('is_active', true)
            ->where('type', 'qa')
            ->where(function ($qq) use ($scopes) {
                // scopes example: [['scope'=>'global','course_id'=>null],['scope'=>'course','course_id'=>5]]
                foreach ($scopes as $s) {
                    $qq->orWhere(function($x) use ($s) {
                        $x->where('scope', $s['scope']);
                        if ($s['course_id'] === null) $x->whereNull('course_id');
                        else $x->where('course_id', $s['course_id']);
                    });
                }
            })
            ->get();

        $best = null;
        $bestScore = 0;

        foreach ($candidates as $item) {
            $pattern = Str::lower(trim((string)$item->question));
            if ($pattern === '') continue;

            similar_text($q, $pattern, $percent);
            // Also keyword boost
            $kwBoost = 0;
            if ($item->keywords) {
                foreach (explode(',', Str::lower($item->keywords)) as $kw) {
                    $kw = trim($kw);
                    if ($kw !== '' && Str::contains($q, $kw)) $kwBoost += 5;
                }
            }
            $score = $percent + $kwBoost;

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $item;
            }
        }

        // threshold
        return ($best && $bestScore >= 82) ? $best : null;
    }
}