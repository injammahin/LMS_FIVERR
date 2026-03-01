<?php

// app/Models/LessonProgress.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    protected $table = 'lesson_progress';

    protected $fillable = [
        'lesson_id','user_id','viewed_at','completed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function lesson() { return $this->belongsTo(Lesson::class); }
    public function user() { return $this->belongsTo(User::class); }
}