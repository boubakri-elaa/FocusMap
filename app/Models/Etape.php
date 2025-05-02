<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Objectif; 
class Etape extends Model
{

    use HasFactory;

    protected $fillable = [
        'objectif_id',
        'titre',
        'description',
    ];
    public function objectif()
    {
        return $this->belongsTo(Objectif::class); // Chaque étape appartient à un objectif
    }
}
