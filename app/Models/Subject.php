<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'division_id',
        'name',
        'slug',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
    public function courses()
    {
        return $this->hasMany(\App\Models\Course::class);
    }
}