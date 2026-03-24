<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentNote extends Model
{
    protected $fillable = [
        'user_id',
        'subject_id',
        'course_id',
        'title',
        'body_html',
        'body_text',
        'excerpt',
        'is_pinned',
        'last_saved_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'last_saved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function attachments()
    {
        return $this->hasMany(StudentNoteAttachment::class);
    }
}