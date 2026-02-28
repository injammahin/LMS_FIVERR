<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'course_id','title','description','attachment',
        'submission_type','grading_type','total_marks','max_attempts',
        'due_at','allow_late','late_until','status'
    ];

    protected $casts = [
        'allow_late' => 'boolean',
        'due_at' => 'datetime',
        'late_until' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}