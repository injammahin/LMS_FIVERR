<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiKbEntry extends Model
{
    protected $fillable = [
        'scope','course_id','type','title','question','answer','body','keywords','is_active','created_by','updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function files()
    {
        return $this->hasMany(AiKbFile::class);
    }
}