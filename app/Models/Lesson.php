<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'position',
        'video_url',
        'content',
        'content_blocks',
    ];

    protected $casts = [
        'content_blocks' => 'array',
         'course_id' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function progress()
    {
        return $this->hasMany(\App\Models\LessonProgress::class);
    }
}