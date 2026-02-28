<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizOption extends Model
{
    protected $fillable = [
        'question_id',
        'option_text',
        'option_image',
        'is_correct',
        'position',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    // ✅ Universal label for views: use $opt->label everywhere
    public function getLabelAttribute(): string
    {
        return (string)($this->option_text ?? '');
    }

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}