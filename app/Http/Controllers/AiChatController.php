<?php

namespace App\Http\Controllers;

use App\Models\AiChatLog;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Assignment;
use App\Services\AI\OpenAIService;
use App\Services\AI\QaRuleMatcher;
use App\Services\AI\VectorStoreResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    public function stream(
        Request $request,
        OpenAIService $openai,
        VectorStoreResolver $resolver,
        QaRuleMatcher $matcher
    ) {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'course_id' => ['nullable'],
        ]);

        $user = $request->user();
        $message = trim((string)$data['message']);
        $courseId = $this->normalizeCourseId($data['course_id'] ?? null);

        // 0) small talk
        if ($small = $this->smallTalkReply($message)) {
            return $this->replySse($user, $courseId, $message, $small, ['source' => 'smalltalk']);
        }

        // strict course access (optional)
        if ($courseId && !$this->userCanAccessCourse($user, $courseId)) {
            $courseId = null;
        }

        // 1) context commands + followups
        if ($ctxText = $this->handleContext($user, $message)) {
            return $this->replySse($user, $courseId, $message, $ctxText, ['source' => 'context']);
        }

        // 2) admin-trained Q/A
        $scopes = [['scope' => 'global', 'course_id' => null]];
        if ($courseId) $scopes[] = ['scope' => 'course', 'course_id' => $courseId];

        $qa = $matcher->match($message, $scopes);
        if ($qa) {
            return $this->replySse($user, $courseId, $message, (string)($qa->answer ?? ''), [
                'source' => 'trained',
                'mode' => 'rule_qa',
                'kb_entry_id' => $qa->id,
            ]);
        }

        // 3) DB course lookup
        if ($courseReply = $this->courseLookupReply($user, $message)) {
            return $this->replySse($user, $courseId, $message, $courseReply, ['source' => 'db_course_lookup']);
        }

        // 4) Local support intents (login/reset/enroll/submit) - quick & accurate
        if ($local = $this->localSupportIntents($message)) {
            return $this->replySse($user, $courseId, $message, $local, ['source' => 'local_support']);
        }

        // 5) Cache identical question
        $cacheKey = 'ai_chat:' . md5($user->id . '|' . ($courseId ?? 0) . '|' . $message);
        if ($cached = Cache::get($cacheKey)) {
            return $this->replySse($user, $courseId, $message, (string)$cached, ['source' => 'cache']);
        }

        // 6) OpenAI fallback (NOT for LMS data guessing)
        $vectorStoreIds = [$resolver->global()->openai_vector_store_id];
        if ($courseId) $vectorStoreIds[] = $resolver->forCourse($courseId)->openai_vector_store_id;

        return $this->emitSse(function () use ($openai, $vectorStoreIds, $user, $courseId, $message, $cacheKey) {
            $brand = $this->brandName();

            $payload = [
                'model' => config('ai_assistant.model'),
                'input' => [
                    [
                        'role' => 'system',
                        'content' =>
                            "You are the official support assistant for {$brand}.\n".
                            "RULES:\n".
                            "1) Use file_search results first if relevant.\n".
                            "2) If not relevant, answer generally.\n".
                            "3) Never mention AI/OpenAI/tools/policies.\n".
                            "4) Do NOT guess LMS-specific data (lessons/quizzes/assignments). Ask a short follow-up instead.\n".
                            "5) Keep replies short & step-by-step.\n"
                    ],
                    ['role' => 'user', 'content' => $message],
                ],
                'tools' => [[
                    'type' => 'file_search',
                    'vector_store_ids' => $vectorStoreIds,
                    'max_num_results' => (int) config('ai_assistant.file_search_max_results', 5),
                ]],
                'stream' => true,
            ];

            $finalText = '';

            try {
                $stream = $openai->createResponseStream($payload);

                $buffer = '';
                while (!$stream->eof()) {
                    $chunk = $stream->read(2048);
                    if ($chunk === '') continue;

                    $buffer .= $chunk;

                    $events = preg_split("/\r?\n\r?\n/", $buffer);
                    $buffer = array_pop($events);

                    foreach ($events as $evt) {
                        $evt = trim($evt);
                        if ($evt === '') continue;

                        $eventName = null;
                        $dataLine = null;

                        foreach (preg_split("/\r?\n/", $evt) as $line) {
                            $line = trim($line);
                            if (str_starts_with($line, 'event:')) $eventName = trim(substr($line, 6));
                            if (str_starts_with($line, 'data:'))  $dataLine  = trim(substr($line, 5));
                        }

                        if (!$dataLine || $dataLine === '[DONE]') continue;

                        $json = json_decode($dataLine, true);
                        if (!is_array($json)) continue;

                        $eventName = $eventName ?: ($json['type'] ?? null);
                        if (!$eventName) continue;

                        if ($eventName === 'response.output_text.delta') {
                            $delta = (string)($json['delta'] ?? '');
                            if ($delta !== '') {
                                $finalText .= $delta;
                                $this->sseDelta($delta);
                            }
                            continue;
                        }

                        if ($eventName === 'response.completed') {
                            if (trim($finalText) === '') {
                                $finalText = $this->fallbackSupportAnswer($message);
                                $this->sseDelta($finalText);
                            }

                            $this->sseDone();

                            AiChatLog::create([
                                'user_id' => $user->id,
                                'course_id' => $courseId,
                                'question' => $message,
                                'answer' => $finalText,
                                'meta' => ['source' => 'openai', 'vector_store_ids' => $vectorStoreIds],
                            ]);

                            Cache::put($cacheKey, $finalText, now()->addMinutes(10));
                            return;
                        }

                        if ($eventName === 'error') {
                            $fallback = $this->fallbackSupportAnswer($message);
                            $this->sseDelta($fallback);
                            $this->sseDone();
                            return;
                        }
                    }
                }

                if (trim($finalText) === '') {
                    $finalText = $this->fallbackSupportAnswer($message);
                    $this->sseDelta($finalText);
                }
                $this->sseDone();
            } catch (\Throwable $e) {
                $fallback = $this->fallbackSupportAnswer($message);
                $this->sseDelta($fallback);
                $this->sseDone();
            }
        });
    }

    // =========================================================
    // Context (course selected + list mapping + lesson focus)
    // =========================================================

    private function ctxKey(int $userId): string
    {
        return "ai_ctx:{$userId}";
    }

    private function getCtx(int $userId): ?array
    {
        $ctx = Cache::get($this->ctxKey($userId));
        return is_array($ctx) ? $ctx : null;
    }

    private function setCtx(int $userId, array $ctx): void
    {
        Cache::put($this->ctxKey($userId), $ctx, now()->addMinutes(30));
    }

    private function clearCtx(int $userId): void
    {
        Cache::forget($this->ctxKey($userId));
    }

    private function handleContext($user, string $message): ?string
    {
        $ctx = $this->getCtx($user->id);
        if (!$ctx) return null;

        $m = strtolower(trim($message));

        // A) course pick list: reply 1/2/3
        if (($ctx['type'] ?? '') === 'course_pick') {
            if (preg_match('/^\d+$/', $m)) {
                $idx = (int)$m - 1;
                $ids = $ctx['course_ids'] ?? [];
                if (!isset($ids[$idx])) {
                    return "⚠️ Please reply with a number between 1 and " . count($ids) . ".";
                }

                $course = Course::with(['subject.division'])
                    ->withCount(['lessons','quizzes','assignments'])
                    ->find((int)$ids[$idx]);

                if (!$course) {
                    $this->clearCtx($user->id);
                    return "⚠️ Sorry, I couldn’t open that course. Please type the course name again.";
                }

                $this->setCtx($user->id, [
                    'type' => 'course_menu',
                    'course_id' => $course->id,
                    'last_lessons_map' => [],
                    'last_quizzes_map' => [],
                    'last_assignments_map' => [],
                    'focus' => null, // lesson focus
                ]);

                return $this->formatCourseDetails($course);
            }

            // any other message: clear pick context
            $this->clearCtx($user->id);
            return null;
        }

        // B) course menu + lesson followups
        if (($ctx['type'] ?? '') === 'course_menu') {
            $courseId = (int)($ctx['course_id'] ?? 0);
            if (!$courseId) return null;

            // ✅ follow-up on last opened lesson (key points / practice / simple steps)
            if (!empty($ctx['focus']) && ($ctx['focus']['type'] ?? null) === 'lesson') {
                $lessonId = (int)($ctx['focus']['id'] ?? 0);
                if ($lessonId > 0) {
                    if ($this->isAskKeyPoints($m)) {
                        return $this->lessonKeyPoints($lessonId);
                    }
                    if ($this->isAskPractice($m)) {
                        return $this->lessonPracticeQuestions($lessonId);
                    }
                    if ($this->isAskSimpleSteps($m)) {
                        return $this->lessonSimpleSteps($lessonId);
                    }
                }
            }

            // lesson list / quiz list / assignment list commands
            $wantLessons = in_array($m, ['1','lesson list','lessons','show lessons','show lesson list'], true);
            $wantQuizzes = in_array($m, ['2','quiz list','quizzes','show quizzes'], true);
            $wantAssign  = in_array($m, ['3','assignment list','assignments','show assignments'], true);

            if ($wantLessons || $wantQuizzes || $wantAssign) {
                $course = Course::with(['lessons','quizzes','assignments'])
                    ->withCount(['lessons','quizzes','assignments'])
                    ->find($courseId);

                if (!$course) return "⚠️ Course not found. Please type the course name again.";

                if ($wantLessons) return $this->formatLessonListAndStoreMap($user->id, $course);
                if ($wantQuizzes) return $this->formatQuizListAndStoreMap($user->id, $course);
                if ($wantAssign)  return $this->formatAssignmentListAndStoreMap($user->id, $course);
            }

            // explain lesson N (N is list number based on last map)
            if (preg_match('/\b(explain|details|describe|what is in|what\'s in)\s+lesson\s+(\d+)\b/i', $message, $mm)) {
                $n = (int)$mm[2];
                return $this->lessonDetailsFromListNumber($user->id, $courseId, $n);
            }

            // explain quiz N
            if (preg_match('/\b(explain|details|describe|what is in|what\'s in)\s+quiz\s+(\d+)\b/i', $message, $mm)) {
                $n = (int)$mm[2];
                return $this->quizDetailsFromListNumber($user->id, $courseId, $n);
            }

            // explain assignment N
            if (preg_match('/\b(explain|details|describe|what is in|what\'s in)\s+assignment\s+(\d+)\b/i', $message, $mm)) {
                $n = (int)$mm[2];
                return $this->assignmentDetailsFromListNumber($user->id, $courseId, $n);
            }
        }

        return null;
    }

    // =========================================================
    // Course lookup (no IDs shown)
    // =========================================================

    private function courseLookupReply($user, string $message): ?string
    {
        $q = Str::of($message)->lower()->replaceMatches('/[^a-z0-9\s]/', ' ')->squish()->toString();

        // "course 1" or "course1"
        if (preg_match('/\bcourse\s*([0-9]+)\b/', $q, $mm)) {
            $id = (int)$mm[1];
            $course = Course::with(['subject.division'])
                ->withCount(['lessons','quizzes','assignments'])
                ->find($id);

            if ($course) {
                $this->setCtx($user->id, [
                    'type' => 'course_menu',
                    'course_id' => $course->id,
                    'last_lessons_map' => [],
                    'last_quizzes_map' => [],
                    'last_assignments_map' => [],
                    'focus' => null,
                ]);
                return $this->formatCourseDetails($course);
            }
        }

        if (mb_strlen($q) < 3) return null;

        $q = str_replace(['tell me about','about','course','subject','details of','details'], '', $q);
        $q = trim(preg_replace('/\s+/', ' ', $q));
        if ($q === '') return null;

        $courses = Course::query()
            ->with(['subject.division'])
            ->withCount(['lessons','quizzes','assignments'])
            ->where('title', 'like', "%{$q}%")
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        if ($courses->isEmpty()) return null;

        if ($courses->count() > 1) {
            $this->setCtx($user->id, [
                'type' => 'course_pick',
                'query' => $q,
                'course_ids' => $courses->pluck('id')->values()->all(),
            ]);

            $lines = [];
            $lines[] = "🔎 **I found multiple courses** for: “{$q}”";
            $lines[] = "";
            foreach ($courses as $i => $c) {
                $lines[] = "• " . ($i + 1) . ") {$c->title}";
            }
            $lines[] = "";
            $lines[] = "✅ Reply with the number to open (example: 1).";
            return implode("\n", $lines);
        }

        $course = $courses->first();

        $this->setCtx($user->id, [
            'type' => 'course_menu',
            'course_id' => $course->id,
            'last_lessons_map' => [],
            'last_quizzes_map' => [],
            'last_assignments_map' => [],
            'focus' => null,
        ]);

        return $this->formatCourseDetails($course);
    }

    private function formatCourseDetails(Course $c): string
    {
        $division = $c->subject?->division?->name;
        $subject  = $c->subject?->name;

        $desc = Str::of((string)($c->description ?? ''))
            ->replace('&nbsp;', ' ')
            ->stripTags()
            ->squish()
            ->limit(240)
            ->toString();

        $lines = [];
        $lines[] = "📘 **{$c->title}**";
        if ($division) $lines[] = "🏫 Division: {$division}";
        if ($subject)  $lines[] = "📚 Subject: {$subject}";
        $lines[] = "";
        $lines[] = "📌 **Course Content**";
        $lines[] = "• Lessons: {$c->lessons_count}";
        $lines[] = "• Quizzes: {$c->quizzes_count}";
        $lines[] = "• Assignments: {$c->assignments_count}";

        if ($desc !== '') {
            $lines[] = "";
            $lines[] = "📝 Overview:";
            $lines[] = $desc;
        }

        $lines[] = "";
        $lines[] = "👉 Choose an option:";
        $lines[] = "1) Lesson list";
        $lines[] = "2) Quiz list";
        $lines[] = "3) Assignment list";
        $lines[] = "";
        $lines[] = "💡 After opening lesson list you can type: “Explain lesson 2”.";

        return implode("\n", $lines);
    }

    // =========================================================
    // Lists (store mapping internally, no IDs shown)
    // =========================================================

    private function formatLessonListAndStoreMap(int $userId, Course $course): string
    {
        $lessons = $course->lessons ? $course->lessons->sortBy(['position', 'id']) : collect();
        if ($lessons->isEmpty()) return "📚 **{$course->title}** has no lessons yet.";

        $map = $lessons->pluck('id')->values()->all();

        $ctx = $this->getCtx($userId) ?? [];
        $ctx['type'] = 'course_menu';
        $ctx['course_id'] = $course->id;
        $ctx['last_lessons_map'] = $map;
        $ctx['focus'] = null;
        $this->setCtx($userId, $ctx);

        $lines = [];
        $lines[] = "📚 **Lesson List — {$course->title}**";
        $lines[] = "";

        $i = 1;
        foreach ($lessons->take(30) as $l) {
            $title = trim((string)($l->title ?? 'Lesson'));
            $lines[] = "• {$i}) {$title}";
            $i++;
        }

        $lines[] = "";
        $lines[] = "✅ Ask like:";
        $lines[] = "• “Explain lesson 1”";
        $lines[] = "• “Give me key points” (after opening a lesson)";
        $lines[] = "• “Give practice questions” (after opening a lesson)";

        return implode("\n", $lines);
    }

    private function formatQuizListAndStoreMap(int $userId, Course $course): string
    {
        $quizzes = $course->quizzes ?? collect();
        if ($quizzes->isEmpty()) return "✅ **{$course->title}** has no quizzes yet.";

        $map = $quizzes->pluck('id')->values()->all();

        $ctx = $this->getCtx($userId) ?? [];
        $ctx['type'] = 'course_menu';
        $ctx['course_id'] = $course->id;
        $ctx['last_quizzes_map'] = $map;
        $ctx['focus'] = null;
        $this->setCtx($userId, $ctx);

        $lines = [];
        $lines[] = "✅ **Quiz List — {$course->title}**";
        $lines[] = "";

        $i = 1;
        foreach ($quizzes->take(30) as $q) {
            $lines[] = "• {$i}) " . ($q->title ?? 'Quiz');
            $i++;
        }

        $lines[] = "";
        $lines[] = "✅ Ask: “Explain quiz 1”";
        return implode("\n", $lines);
    }

    private function formatAssignmentListAndStoreMap(int $userId, Course $course): string
    {
        $assignments = $course->assignments ?? collect();
        if ($assignments->isEmpty()) return "📝 **{$course->title}** has no assignments yet.";

        $map = $assignments->pluck('id')->values()->all();

        $ctx = $this->getCtx($userId) ?? [];
        $ctx['type'] = 'course_menu';
        $ctx['course_id'] = $course->id;
        $ctx['last_assignments_map'] = $map;
        $ctx['focus'] = null;
        $this->setCtx($userId, $ctx);

        $lines = [];
        $lines[] = "📝 **Assignment List — {$course->title}**";
        $lines[] = "";

        $i = 1;
        foreach ($assignments->take(30) as $a) {
            $lines[] = "• {$i}) " . ($a->title ?? 'Assignment');
            $i++;
        }

        $lines[] = "";
        $lines[] = "✅ Ask: “Explain assignment 1”";
        return implode("\n", $lines);
    }

    // =========================================================
    // Details (uses list-number mapping, sets focus lesson)
    // =========================================================

    private function lessonDetailsFromListNumber(int $userId, int $courseId, int $n): string
    {
        $ctx = $this->getCtx($userId) ?? [];
        $map = $ctx['last_lessons_map'] ?? [];

        if (!isset($map[$n - 1])) {
            return "⚠️ I can’t find lesson number {$n}.\nPlease type **Lesson list** and then choose a number (1,2,3...).";
        }

        $lessonId = (int)$map[$n - 1];
        $lesson = Lesson::where('course_id', $courseId)->where('id', $lessonId)->first();

        if (!$lesson) {
            return "⚠️ Lesson not found. Please open Lesson list again.";
        }

        // set focus
        $ctx['focus'] = ['type' => 'lesson', 'id' => $lesson->id];
        $this->setCtx($userId, $ctx);

        return $this->formatLessonDetails($lesson);
    }

    private function quizDetailsFromListNumber(int $userId, int $courseId, int $n): string
    {
        $ctx = $this->getCtx($userId) ?? [];
        $map = $ctx['last_quizzes_map'] ?? [];

        if (!isset($map[$n - 1])) {
            return "⚠️ I can’t find quiz number {$n}.\nPlease type **2** to open Quiz list, then choose a number.";
        }

        $quizId = (int)$map[$n - 1];
        $quiz = Quiz::where('course_id', $courseId)->where('id', $quizId)->first();

        if (!$quiz) return "⚠️ Quiz not found. Please open Quiz list again.";

        // focus could be quiz if you want later
        $ctx['focus'] = ['type' => 'quiz', 'id' => $quiz->id];
        $this->setCtx($userId, $ctx);

        return $this->formatQuizDetails($quiz);
    }

    private function assignmentDetailsFromListNumber(int $userId, int $courseId, int $n): string
    {
        $ctx = $this->getCtx($userId) ?? [];
        $map = $ctx['last_assignments_map'] ?? [];

        if (!isset($map[$n - 1])) {
            return "⚠️ I can’t find assignment number {$n}.\nPlease type **3** to open Assignment list, then choose a number.";
        }

        $aid = (int)$map[$n - 1];
        $a = Assignment::where('course_id', $courseId)->where('id', $aid)->first();

        if (!$a) return "⚠️ Assignment not found. Please open Assignment list again.";

        $ctx['focus'] = ['type' => 'assignment', 'id' => $a->id];
        $this->setCtx($userId, $ctx);

        return $this->formatAssignmentDetails($a);
    }

    private function formatLessonDetails(Lesson $lesson): string
    {
        $title = trim((string)($lesson->title ?? 'Lesson'));

        $desc = Str::of((string)($lesson->description ?? ''))
            ->replace('&nbsp;', ' ')
            ->stripTags()
            ->squish()
            ->toString();

        $lines = [];
        $lines[] = "📘 **{$title}**";
        $lines[] = "";

        if ($desc !== '') {
            $lines[] = "📝 **Lesson Notes**";
            $lines[] = $desc;
        } else {
            $lines[] = "📝 **Lesson Notes**";
            $lines[] = "No text notes available for this lesson.";
        }

        $lines[] = "";
        $lines[] = "👉 Next you can type:";
        $lines[] = "• **Give me key points**";
        $lines[] = "• **Give practice questions**";
        $lines[] = "• **Explain in simple steps**";

        return implode("\n", $lines);
    }

    private function formatQuizDetails(Quiz $quiz): string
    {
        $title = trim((string)($quiz->title ?? 'Quiz'));

        return "✅ **{$title}**\n\n"
            . "📌 To attempt:\n"
            . "1) Open Course → Quiz\n"
            . "2) Click **Start**\n"
            . "3) Answer all questions\n"
            . "4) Click **Submit**";
    }

    private function formatAssignmentDetails(Assignment $a): string
    {
        $title = trim((string)($a->title ?? 'Assignment'));

        $desc = Str::of((string)($a->description ?? ''))
            ->replace('&nbsp;', ' ')
            ->stripTags()
            ->squish()
            ->toString();

        $lines = [];
        $lines[] = "📝 **{$title}**";

        if ($desc !== '') {
            $lines[] = "";
            $lines[] = "📌 Instructions:";
            $lines[] = $desc;
        }

        $lines[] = "";
        $lines[] = "✅ To submit:";
        $lines[] = "1) Open Course → Assignment";
        $lines[] = "2) Upload file / write answer";
        $lines[] = "3) Click **Submit**";

        return implode("\n", $lines);
    }

    // =========================================================
    // Lesson followups (key points / practice / simple steps)
    // =========================================================

    private function isAskKeyPoints(string $m): bool
    {
        return str_contains($m, 'key point')
            || str_contains($m, 'main point')
            || str_contains($m, 'summary')
            || str_contains($m, 'highlight');
    }

    private function isAskPractice(string $m): bool
    {
        return str_contains($m, 'practice')
            || str_contains($m, 'exercise')
            || str_contains($m, 'questions')
            || str_contains($m, 'quiz me');
    }

    private function isAskSimpleSteps(string $m): bool
    {
        return str_contains($m, 'simple')
            || str_contains($m, 'easy steps')
            || str_contains($m, 'step by step')
            || str_contains($m, 'simplify');
    }

    private function lessonKeyPoints(int $lessonId): string
    {
        $lesson = Lesson::find($lessonId);
        if (!$lesson) return "⚠️ Lesson not found. Please open the lesson again.";

        $text = $this->clean($lesson->description ?? '');
        if ($text === '') return "✅ Key points:\n• This lesson has no text notes saved.";

        // Very safe: extract tasks from text (no hallucination)
        $points = $this->extractTaskBullets($text);

        $lines = [];
        $lines[] = "✨ **Key Points**";
        $lines[] = "• " . implode("\n• ", $points);

        return implode("\n", $lines);
    }

    private function lessonSimpleSteps(int $lessonId): string
    {
        $lesson = Lesson::find($lessonId);
        if (!$lesson) return "⚠️ Lesson not found. Please open the lesson again.";

        $text = $this->clean($lesson->description ?? '');
        if ($text === '') return "✅ Simple steps:\n1) Open the lesson\n2) Follow the worksheet/tasks";

        $tasks = $this->extractTaskBullets($text);

        $lines = [];
        $lines[] = "✅ **Simple Steps**";
        $i = 1;
        foreach ($tasks as $t) {
            $lines[] = "{$i}) {$t}";
            $i++;
        }

        return implode("\n", $lines);
    }

    private function lessonPracticeQuestions(int $lessonId): string
    {
        $lesson = Lesson::find($lessonId);
        if (!$lesson) return "⚠️ Lesson not found. Please open the lesson again.";

        $text = strtolower($this->clean($lesson->description ?? ''));

        // Generate practice based on what the lesson actually contains
        $lines = [];
        $lines[] = "🧩 **Practice Questions**";

        $hasTens = str_contains($text, 'tens') || str_contains($text, '10, 20') || str_contains($text, 'by tens');
        $hasOnes = str_contains($text, 'by ones') || str_contains($text, '1 to 100') || str_contains($text, 'ones');

        if ($hasTens) {
            $lines[] = "";
            $lines[] = "A) Count by tens:";
            $lines[] = "1) Fill in: 10, __, 30, __, 50, __, 70, __, 90, __";
            $lines[] = "2) Write the next 5 numbers after 40 counting by tens.";
            $lines[] = "3) Circle the numbers that are multiples of 10: 12, 20, 35, 50, 77, 90";
        }

        if ($hasOnes) {
            $lines[] = "";
            $lines[] = "B) Count by ones:";
            $lines[] = "1) Fill in: 17, 18, __, 20, __, 22";
            $lines[] = "2) Write numbers from 31 to 40.";
            $lines[] = "3) What comes just before 50? What comes just after 50?";
        }

        if (!$hasTens && !$hasOnes) {
            $lines[] = "";
            $lines[] = "1) Read the lesson notes again and practice the tasks mentioned.";
            $lines[] = "2) Tell me what topic you want practice on (counting / addition / subtraction etc.).";
        }

        $lines[] = "";
        $lines[] = "✅ If you want answers, say: “Show answers”.";

        return implode("\n", $lines);
    }

    private function extractTaskBullets(string $text): array
    {
        // Basic extraction from lesson notes (safe, no guessing)
        $lines = preg_split("/\r\n|\n|\r/", $text);
        $bullets = [];

        foreach ($lines as $l) {
            $l = trim($l);
            if ($l === '') continue;

            // keep lines that look like tasks
            if (preg_match('/\b(count|practice|write|complete|download|print)\b/i', $l)) {
                $bullets[] = rtrim($l, ".! ");
            }
        }

        // if none found, fall back to first 3 sentences
        if (empty($bullets)) {
            $sent = preg_split('/(?<=[.!?])\s+/', $text);
            foreach (array_slice($sent, 0, 3) as $s) {
                $s = trim($s);
                if ($s !== '') $bullets[] = rtrim($s, ".! ");
            }
        }

        // limit
        $bullets = array_slice($bullets, 0, 6);
        if (empty($bullets)) $bullets[] = "Follow the lesson instructions and worksheet.";

        return $bullets;
    }

    private function clean(string $html): string
    {
        return Str::of($html)
            ->replace('&nbsp;', ' ')
            ->stripTags()
            ->squish()
            ->toString();
    }

    // =========================================================
    // Local Support Intents
    // =========================================================

    private function localSupportIntents(string $message): ?string
    {
        $m = strtolower($message);

        if (str_contains($m, 'login') || str_contains($m, 'sign in')) {
            return "🔐 **Login Help**\n\n"
                . "1) Go to the login page\n"
                . "2) Enter your email & password\n"
                . "3) Click **Login**\n\n"
                . "If you forgot password, type: **reset password**.\n"
                . "If your account is inactive, contact admin to activate it.";
        }

        if (str_contains($m, 'reset') && str_contains($m, 'password')) {
            return "🔁 **Reset Password**\n\n"
                . "1) Go to login page\n"
                . "2) Click **Forgot Password**\n"
                . "3) Enter your email\n"
                . "4) Check email (Inbox/Spam)\n"
                . "5) Set new password\n\n"
                . "If email doesn’t arrive, contact admin with your registered email.";
        }

        if (str_contains($m, 'enroll') || str_contains($m, 'admission')) {
            return "📚 **How to Enroll**\n\n"
                . "1) Go to Subjects / Courses page\n"
                . "2) Open the course you want\n"
                . "3) Click **Enroll** (if available)\n\n"
                . "If enrollment is managed by admin, tell me your **Division** and **Course name**.";
        }

        if (str_contains($m, 'submit') && str_contains($m, 'assignment')) {
            return "📝 **Submit Assignment**\n\n"
                . "1) Open your course\n"
                . "2) Go to Assignments\n"
                . "3) Open the assignment\n"
                . "4) Upload file / write answer\n"
                . "5) Click **Submit**";
        }

        return null;
    }

    // =========================================================
    // Smalltalk + fallback
    // =========================================================

    private function brandName(): string
    {
        return "Ambala IT LMS";
    }

    private function smallTalkReply(string $message): ?string
    {
        $m = strtolower(trim($message));

        if (in_array($m, ['hi','hello','hey'], true)) {
            return "Hello 👋\nHow can I help you today?\n• Courses • Lessons • Quizzes • Assignments • Login • Certificate";
        }

        if (preg_match('/\bhow are you\b|\bhow r u\b|\bhow r you\b/i', $m)) {
            return "I’m doing great 😊\nTell me what you need help with in the LMS.";
        }

        if (preg_match('/\bwho are you\b|\bwhat are you\b|\bwhat is this\b/i', $m)) {
            return "I’m the {$this->brandName()} support assistant.\nI can help with login, courses, lessons, quizzes and assignments.";
        }

        return null;
    }

    private function fallbackSupportAnswer(string $message): string
    {
        return "✅ I can help.\n\nTry one of these:\n• Type: **course 1** (or a course name)\n• Then type: **1** for Lesson list\n• Then type: **Explain lesson 2**";
    }

    // =========================================================
    // Access + Utils + SSE
    // =========================================================

    private function normalizeCourseId($courseId): ?int
    {
        if ($courseId === null || $courseId === '') return null;
        return is_numeric($courseId) ? (int)$courseId : null;
    }

    private function userCanAccessCourse($user, int $courseId): bool
    {
        if (($user->role ?? null) === 'admin') return true;

        if (method_exists($user, 'courses')) {
            return $user->courses()->where('courses.id', $courseId)->exists();
        }

        // Best-effort pivots
        $role = $user->role ?? 'student';

        $pivots = [
            'student' => [
                ['table' => 'course_user', 'user_col' => 'user_id'],
                ['table' => 'course_student', 'user_col' => 'student_id'],
                ['table' => 'course_students', 'user_col' => 'student_id'],
            ],
            'teacher' => [
                ['table' => 'course_teacher', 'user_col' => 'teacher_id'],
                ['table' => 'teacher_course', 'user_col' => 'teacher_id'],
                ['table' => 'course_teachers', 'user_col' => 'teacher_id'],
            ],
            'staff' => [
                ['table' => 'course_staff', 'user_col' => 'staff_id'],
                ['table' => 'staff_course', 'user_col' => 'staff_id'],
                ['table' => 'course_staffs', 'user_col' => 'staff_id'],
            ],
        ];

        foreach ($pivots[$role] ?? [] as $p) {
            if (Schema::hasTable($p['table'])) {
                if (DB::table($p['table'])
                    ->where($p['user_col'], $user->id)
                    ->where('course_id', $courseId)
                    ->exists()
                ) return true;
            }
        }

        // Change to false if you want strict access
        return true;
    }

    private function replySse($user, ?int $courseId, string $question, string $answer, array $meta = [])
    {
        return $this->emitSse(function () use ($user, $courseId, $question, $answer, $meta) {
            $this->sseDelta($answer);
            $this->sseDone();

            AiChatLog::create([
                'user_id' => $user->id,
                'course_id' => $courseId,
                'question' => $question,
                'answer' => $answer,
                'meta' => $meta,
            ]);
        });
    }

    private function emitSse(\Closure $fn)
    {
        return response()->stream(function () use ($fn) {
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', '0');
            $fn();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function sseDelta(string $text): void
    {
        echo "event: delta\n";
        echo 'data: ' . json_encode(['delta' => $text], JSON_UNESCAPED_UNICODE) . "\n\n";
        @ob_flush(); flush();
    }

    private function sseDone(): void
    {
        echo "event: done\n";
        echo "data: {}\n\n";
        @ob_flush(); flush();
    }
}