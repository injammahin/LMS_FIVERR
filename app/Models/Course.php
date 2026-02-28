<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'subject_id',
        'title',
        'slug',
        'description',
        'thumbnail',
        'status',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
    public function lessons()
    {
        return $this->hasMany(\App\Models\Lesson::class)->orderBy('position');
    }
    public function quizzes()
    {
        return $this->hasMany(\App\Models\Quiz::class);
    }
    public function assignments()
    {
        return $this->hasMany(\App\Models\Assignment::class);
    }
    public function teachers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'course_teacher', 'course_id', 'teacher_id')
            ->withTimestamps();
    }
}