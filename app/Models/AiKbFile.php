<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiKbFile extends Model
{
    protected $fillable = [
        'scope','course_id','ai_kb_entry_id','original_name','stored_path','mime','size',
        'openai_file_id','openai_vector_store_id','status','last_error','created_by'
    ];

    public function entry()
    {
        return $this->belongsTo(AiKbEntry::class, 'ai_kb_entry_id');
    }
}