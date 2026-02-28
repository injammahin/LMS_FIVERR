<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuizAttemptController extends Controller
{
    public function start(Quiz $quiz)
    {
        $user = auth()->user();
        $quiz->load('course.subject.division');

        // ✅ Division guard
        $divisionId = optional(optional($quiz->course->subject)->division)->id;
        abort_if((int)$user->division_id !== (int)$divisionId, 403);

        // ✅ published only
        abort_if(($quiz->status ?? 'draft') !== 'published', 403);

        // ✅ max attempts (count only submitted)
        if (!empty($quiz->max_attempts)) {
            $used = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('user_id', $user->id)
                ->whereNotNull('submitted_at')
                ->count();

            abort_if($used >= (int)$quiz->max_attempts, 403);
        }

        // ✅ resume existing in_progress
        $attempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if (!$attempt) {
            $now = now();
            $limitMinutes = (int)($quiz->time_limit_minutes ?? 0);
            $durationSeconds = $limitMinutes > 0 ? $limitMinutes * 60 : 0;
            $endsAt = $durationSeconds > 0 ? $now->copy()->addSeconds($durationSeconds) : null;

            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'started_at' => $now,
                'ends_at' => $endsAt,
                'duration_seconds' => $durationSeconds,
                'status' => 'in_progress',
                'score' => 0,
                'total' => 0,
            ]);
        }

        return redirect()->route('student.quiz.attempt.show', $attempt->id);
    }

    public function show(QuizAttempt $attempt)
    {
        $user = auth()->user();
        abort_if((int)$attempt->user_id !== (int)$user->id, 403);
        abort_if($attempt->status !== 'in_progress', 403);

        $attempt->load([
            'quiz.course.subject.division',
            'quiz.questions.options' => fn($q) => $q->orderBy('position'),
            'answers',
        ]);

        $quiz = $attempt->quiz;
        $course = $quiz->course;

        // ✅ Division guard
        $divisionId = optional(optional($course->subject)->division)->id;
        abort_if((int)$user->division_id !== (int)$divisionId, 403);

        // ✅ if time ended => auto submit
        if ((int)($attempt->duration_seconds ?? 0) > 0) {
            $remaining = $this->remainingSeconds($attempt);
            if ($remaining <= 0) {
                return $this->forceAutoSubmit($attempt);
            }
        }

        $questions = $quiz->questions->sortBy('position')->values();
        $answers = $attempt->answers->keyBy('question_id');
        $timeLimitMinutes = (int)($quiz->time_limit_minutes ?? 0);

        // ✅ always use DB ends_at if exists, else calculate and save (safe)
        $endsAt = $attempt->ends_at;
        if ($timeLimitMinutes > 0 && !$endsAt) {
            $endsAt = ($attempt->started_at ?? now())->copy()->addMinutes($timeLimitMinutes);
            $attempt->ends_at = $endsAt;
            $attempt->duration_seconds = $timeLimitMinutes * 60;
            $attempt->save();
        }

        $remainingSeconds = $this->remainingSeconds($attempt);

        return view('student.quiz_attempt', [
            'attempt' => $attempt,
            'quiz' => $quiz,
            'course' => $course,
            'questions' => $questions,
            'answers' => $answers,
            'remainingSeconds' => $remainingSeconds,
            'timeLimitMinutes' => $timeLimitMinutes,
            'endsAt' => $endsAt?->toIso8601String(),
        ]);
    }

    public function submit(Request $request, QuizAttempt $attempt)
    {
        $user = auth()->user();
        abort_if((int)$attempt->user_id !== (int)$user->id, 403);
        abort_if($attempt->status !== 'in_progress', 403);

        $attempt->load([
            'quiz.questions.options' => fn($q) => $q->orderBy('position'),
            'quiz.course.subject.division',
            'answers',
        ]);

        $quiz = $attempt->quiz;
        $course = $quiz->course;

        // ✅ Division guard
        $divisionId = optional(optional($course->subject)->division)->id;
        abort_if((int)$user->division_id !== (int)$divisionId, 403);

        // ✅ validate only file questions
        foreach ($quiz->questions as $q) {
            if ($q->type === 'file') {
                $request->validate([
                    "answers.{$q->id}" => ['nullable','file','max:10240','mimes:pdf,doc,docx,png,jpg,jpeg,webp,zip']
                ]);
            }
        }

        DB::transaction(function () use ($request, $attempt, $quiz) {

            $score = 0;
            $total = 0;

            foreach ($quiz->questions as $q) {
                $total += (int)$q->marks;

                $inputKey = "answers.{$q->id}";
                $payload = [];
                $filePath = null;

                // ✅ build payload per question type
                if ($q->type === 'true_false') {
                    $val = $request->input($inputKey);
                    if ($val !== null) $payload = ['value' => ($val === 'true')];
                }

                if ($q->type === 'single_choice') {
                    $optId = $request->input($inputKey);
                    if ($optId) $payload = ['option_id' => (int)$optId];
                }

                if ($q->type === 'multiple_choice') {
                    $optIds = (array)$request->input($inputKey, []);
                    $payload = ['option_ids' => array_values(array_map('intval', $optIds))];
                }

                if ($q->type === 'text') {
                    $text = trim((string)$request->input($inputKey, ''));
                    if ($text !== '') $payload = ['text' => $text];
                }

                if ($q->type === 'file') {
                    if ($request->hasFile($inputKey)) {
                        $existing = QuizAttemptAnswer::where('attempt_id', $attempt->id)
                            ->where('question_id', $q->id)
                            ->first();

                        if ($existing?->file_path) {
                            Storage::disk('public')->delete($existing->file_path);
                        }

                        $filePath = $request->file($inputKey)->store('quiz_submissions', 'public');
                        $payload = ['file' => true];
                    }
                }

                // ✅ grade objective
                $isObjective = in_array($q->type, ['true_false','single_choice','multiple_choice']);
                $isManual = in_array($q->type, ['text','file']);

                $isCorrect = null;
                $awarded = 0;

                if ($isObjective && !empty($payload)) {
                    [$isCorrect, $awarded] = $this->gradeObjective($q, $payload);
                    if ($isCorrect) $score += $awarded;
                }

                // ✅ manual review later
                if ($isManual) {
                    $isCorrect = null;
                    $awarded = 0;
                }

                QuizAttemptAnswer::updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $q->id],
                    [
                        'answer_json' => $payload,
                        'file_path' => $filePath,
                        'is_correct' => $isCorrect,
                        'awarded_marks' => $awarded,
                    ]
                );
            }

            // ✅ IMPORTANT: your attempt table has score + total + status
            $attempt->update([
                'submitted_at' => now(),
                'status' => 'submitted',
                'score' => $score,
                'total' => $total,
            ]);
        });

        return redirect()->route('student.quiz.attempt.result', $attempt->id)
            ->with('success', 'Quiz submitted successfully.');
    }

    public function result(QuizAttempt $attempt)
    {
        $user = auth()->user();
        abort_if((int)$attempt->user_id !== (int)$user->id, 403);
        abort_if($attempt->status !== 'submitted', 403);

        $attempt->load([
            'quiz.course',
            'quiz.questions.options' => fn($q) => $q->orderBy('position'),
            'answers',
        ]);

        $quiz = $attempt->quiz;
        $course = $quiz->course;

        $answers = $attempt->answers->keyBy('question_id');

        $objectiveScore = 0;
        $objectiveTotal = 0;
        $pendingReview = 0;

        foreach ($quiz->questions as $q) {
            $ans = $answers->get($q->id);

            if (in_array($q->type, ['true_false', 'single_choice', 'multiple_choice'])) {
                $objectiveTotal += (int)$q->marks;
                $objectiveScore += (int)($ans?->awarded_marks ?? 0);
            }

            if (in_array($q->type, ['text', 'file'])) {
                $hasAnswer = !empty($ans?->answer_json) || !empty($ans?->file_path);
                if ($hasAnswer) $pendingReview++;
            }
        }

        return view('student.quiz_result', [
            'attempt' => $attempt,
            'quiz' => $quiz,
            'course' => $course,
            'questions' => $quiz->questions->sortBy('position')->values(),
            'answers' => $answers,
            'pendingReview' => $pendingReview,
            'objectiveScore' => $objectiveScore,
            'objectiveTotal' => $objectiveTotal,
        ]);
    }

    private function gradeObjective($question, array $payload): array
    {
        $marks = (int)$question->marks;

        if ($question->type === 'true_false') {
            $given = $payload['value'] ?? null;

            $correctOption = $question->options->firstWhere('is_correct', 1);
            $correctVal = null;
            if ($correctOption) {
                // if you store TF option as "true"/"false" in option_text
                $v = strtolower((string)($correctOption->option_text ?? ''));
                $correctVal = $v === 'true' ? true : ($v === 'false' ? false : null);
            }

            $ok = ($given !== null && $correctVal !== null && (bool)$given === (bool)$correctVal);
            return [$ok, $ok ? $marks : 0];
        }

        if ($question->type === 'single_choice') {
            $given = (int)($payload['option_id'] ?? 0);
            $correct = $question->options->firstWhere('is_correct', 1);
            $ok = $correct && $given === (int)$correct->id;
            return [$ok, $ok ? $marks : 0];
        }

        if ($question->type === 'multiple_choice') {
            $given = array_map('intval', (array)($payload['option_ids'] ?? []));
            sort($given);

            $correctIds = $question->options->where('is_correct', 1)->pluck('id')->map(fn($v)=>(int)$v)->toArray();
            sort($correctIds);

            $ok = ($given === $correctIds);
            return [$ok, $ok ? $marks : 0];
        }

        return [false, 0];
    }

    private function remainingSeconds(QuizAttempt $attempt): int
    {
        $duration = (int)($attempt->duration_seconds ?? 0);
        if ($duration <= 0 || !$attempt->ends_at) return 0;

        $rem = now()->diffInSeconds($attempt->ends_at, false);
        return $rem > 0 ? $rem : 0;
    }

    private function forceAutoSubmit(QuizAttempt $attempt)
    {
        $attempt->submitted_at = now();
        $attempt->status = 'submitted';
        $attempt->save();

        return redirect()->route('student.quiz.attempt.result', $attempt->id)
            ->with('success', 'Time is over. Quiz auto-submitted.');
    }
}