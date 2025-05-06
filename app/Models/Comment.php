<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['objectif_id', 'user_id', 'content'];

    public function objectif()
    {
        return $this->belongsTo(Objectif::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}