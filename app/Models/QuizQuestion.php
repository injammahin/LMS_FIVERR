<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $fillable = [
        'quiz_id',
        'type',
        'question',
        'question_image',
        'explanation',
        'marks',
        'position',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options()
    {
        return $this->hasMany(QuizOption::class, 'question_id')->orderBy('position');
    }

    public function needsOptions(): bool
    {
        return in_array($this->type, ['single_choice', 'multiple_choice', 'true_false'], true);
    }
}