<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection $notifications
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection $unreadNotifications
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'division_id',      
        'name',
        'email',
        'username',
        'password',
        'plain_password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function coursesTeaching(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Course::class,
            'course_teacher',
            'teacher_id',
            'course_id'
        )->withTimestamps();
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Division::class, 'division_id');
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(\App\Models\LessonProgress::class, 'user_id');
    }
    public function coursesSupporting()
    {
        return $this->belongsToMany(\App\Models\Course::class, 'course_staff', 'staff_id', 'course_id')
            ->withTimestamps();
    }
    public function quizAttempts()
    {
        return $this->hasMany(\App\Models\QuizAttempt::class, 'user_id');
    }

    public function assignmentSubmissions()
    {
        return $this->hasMany(\App\Models\AssignmentSubmission::class, 'user_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(\App\Models\Message::class,'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(\App\Models\Message::class,'receiver_id');
    }
    public function courses()
    {
        return $this->belongsToMany(
            \App\Models\Course::class,
            'course_teacher',   // pivot table
            'teacher_id',
            'course_id'
        );
    }
}