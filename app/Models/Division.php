<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
    ];
    public function subjects()
    {
        return $this->hasMany(\App\Models\Subject::class);
    }
    
}