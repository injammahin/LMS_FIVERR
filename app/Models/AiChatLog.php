<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiChatLog extends Model
{
    protected $fillable = [
        'user_id','course_id','question','answer','meta','rating'
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}