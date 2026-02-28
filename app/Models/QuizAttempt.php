<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id','user_id','started_at','submitted_at','score','total_marks','status'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'ends_at' => 'datetime', 
        'duration_seconds' => 'integer',
        
    ];

    public function quiz() { return $this->belongsTo(Quiz::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function answers() { return $this->hasMany(QuizAttemptAnswer::class, 'attempt_id'); }
}