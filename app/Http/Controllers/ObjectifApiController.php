<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Objectif;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ObjectifApiController extends Controller
{
    public function index(): JsonResponse
    {
        $objectifs = Objectif::where('user_id', Auth::id())->get();

        $data = $objectifs->map(function ($objectif) {
            return [
                'user_id' => $objectif->user_id,
                'titre' => $objectif->titre,
                'description' => $objectif->description,
                'deadline' => $objectif->deadline ? $objectif->deadline->format('Y-m-d') : null, // Formater la date si elle existe
                'lieu' => $objectif->lieu,
                'latitude' => $objectif->latitude,
                'longitude' => $objectif->longitude,
                'type' => $objectif->type,
                'visibility' =>$objectif->visibility,
                // Tu peux ajouter d'autres informations ici si nécessaire
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200, [], JSON_PRETTY_PRINT);
    }
}