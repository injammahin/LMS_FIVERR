<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttemptAnswer extends Model
{
    protected $fillable = [
        'attempt_id',
        'question_id',
        'answer_json',
        'file_path',
        'is_correct',
        'awarded_marks',
    ];

    protected $casts = [
        'answer_json' => 'array',
        'is_correct' => 'boolean',
        'awarded_marks' => 'integer',
    ];

    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}