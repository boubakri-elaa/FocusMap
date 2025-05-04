<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Objectif extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'titre',
        'description',
        'deadline',
        'lieu',
        'latitude',
        'longitude',
        'type',
    ];

    public function etapes(): HasMany
    {
        return $this->hasMany(Etape::class);
    }
    public function isCompleted()
{
    // Vérifie si l'objectif a des étapes
    if ($this->etapes->isEmpty()) {
        return false;
    }
    
    // Vérifie si toutes les étapes sont complétées
    return $this->etapes->every(function ($etape) {
        return $this->user->completedEtapes->contains($etape->id);
    });
}
public function user()
{
    return $this->belongsTo(User::class);
}
public function badge()
{
    return $this->belongsTo(Badge::class);
}

public function getProgressColor($user)
{
    $total = $this->etapes->count();
    $completed = $this->etapes->whereIn('id', $user->completedEtapes->pluck('id'))->count();

    if ($total === 0 || $completed === 0) {
        return 'objectif-pending'; // rouge
    } elseif ($completed < $total) {
        return 'objectif-inprogress'; // jaune
    } else {
        return 'objectif-done'; // vert
    }
}
}
