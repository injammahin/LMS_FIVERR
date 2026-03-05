<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiVectorStore extends Model
{
    protected $fillable = [
        'scope','course_id','name','openai_vector_store_id','status','last_synced_at'
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];
}