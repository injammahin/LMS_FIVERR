<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id','user_id',
        'submission_text','submission_file','submitted_at','status',
        'marks_awarded','is_passed','feedback','feedback_file'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'is_passed' => 'boolean',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}