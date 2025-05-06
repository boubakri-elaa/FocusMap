<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    protected $fillable = ['objective_id', 'description', 'milestone_date', 'image_url'];
    protected $casts = [
        'milestone_date' => 'date', // Or 'datetime' if you need time as well
    ];
}