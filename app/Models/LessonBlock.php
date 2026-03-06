<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonBlock extends Model
{
    protected $fillable = [
        'lesson_id',
        'type',
        'text',
        'video_url',
        'file_path'
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}