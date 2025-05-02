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
}
