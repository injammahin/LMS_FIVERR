<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentNoteAttachment extends Model
{
    protected $fillable = [
        'student_note_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    public function note()
    {
        return $this->belongsTo(StudentNote::class, 'student_note_id');
    }
}