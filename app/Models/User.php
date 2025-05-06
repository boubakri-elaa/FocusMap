<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime'];

    public function objectifs()
    {
        return $this->hasMany(Objectif::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')->withTimestamps();
    }

    public function hasBadge($badgeId)
    {
        return $this->badges()->where('badge_id', $badgeId)->exists();
    }

    public function completedEtapes()
    {
        return $this->belongsToMany(Etape::class, 'user_etape_completions')->withTimestamps();
    }

    public function getCompletionPercentage()
    {
        // Count total steps across all user objectives
        $total = $this->objectifs()->withCount('etapes')->get()->sum('etapes_count');
        $completed = $this->completedEtapes()->count();
        return $total > 0 ? round(($completed / $total) * 100) : 0;
    }
    public function friends()
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id')
                    ->withTimestamps();
    }

    public function friendRequests()
    {
        return $this->belongsToMany(User::class, 'friends', 'friend_id', 'user_id')
                    ->withTimestamps();
    }
    public function comments()
{
    return $this->hasMany(Comment::class);
}
}