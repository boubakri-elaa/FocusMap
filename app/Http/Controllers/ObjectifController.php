<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Objectif;
use App\Models\Etape;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ObjectifController extends Controller
{

    protected $geocodingApiUrl = 'https://api.opencagedata.com/geocode/v1/json';
    public function store(Request $request)
{
    $request->validate([
        'titre' => 'required|string|max:255',
        'description' => 'required',
        'deadline' => 'nullable|date',
        'lieu' => 'nullable|string|max:255',
        'type' => 'required|string',
        'etapes.*.titre' => 'required|string|max:255',
        'etapes.*.description' => 'nullable|string',
    ]);

    // Création de l'objectif
    $objectif = Objectif::create([
        'user_id' => Auth::id(),
        'titre' => $request->titre,
        'description' => $request->description,
        'deadline' => $request->deadline,
        'lieu' => $request->lieu,
        'type' => $request->type,
    ]);

    // Création des étapes associées
    foreach ($request->etapes as $etapeData) {
        Etape::create([
            'objectif_id' => $objectif->id,
            'titre' => $etapeData['titre'],
            'description' => $etapeData['description'] ?? '',
        ]);
    }

    // Géocoder et enregistrer les coordonnées
    $this->geocodeAndSaveCoordinates($objectif);

    return redirect()->route('userspace')->with('success', 'Objectif et étapes ajoutés avec succès !');
}
//fonction pour mettre a jour l'objectif
public function update(Request $request, Objectif $objectif)
{
    $request->validate([
        'titre' => 'required|string|max:255',
        'description' => 'required',
        'deadline' => 'nullable|date',
        'lieu' => 'nullable|string|max:255',
        'type' => 'required|string',
        'etapes.*.titre' => 'required|string|max:255',
        'etapes.*.description' => 'nullable|string',
    ]);

    $objectif->update([
        'titre' => $request->titre,
        'description' => $request->description,
        'deadline' => $request->deadline,
        'lieu' => $request->lieu,
        'type' => $request->type,
    ]);

    // Supprimer et recréer les étapes
    $objectif->etapes()->delete();
    foreach ($request->etapes as $etapeData) {
        $objectif->etapes()->create([
            'titre' => $etapeData['titre'],
            'description' => $etapeData['description'] ?? '',
        ]);
    }

    // Géocoder et enregistrer les coordonnées si le lieu a été modifié
    if ($request->filled('lieu') && $objectif->isDirty('lieu')) {
        $this->geocodeAndSaveCoordinates($objectif);
    }

    return back()->with('success', 'Objectif mis à jour !');
}
    public function destroy(Objectif $objectif)
    {
        if ($objectif->user_id === Auth::id()) {
            $objectif->etapes()->delete(); // Supprimer d'abord les étapes liées
            $objectif->delete();
        }
        return back()->with('success', 'Objectif et étapes supprimés !');
    }

    public function index()
{
    $objectifs = Objectif::with('etapes')->where('user_id', Auth::id())->get();
    return view('userspace', compact('objectifs'));
}

protected function geocodeAndSaveCoordinates(Objectif $objectif)
{
    if ($objectif->lieu) {
        $params = [
            'q' => $objectif->lieu,
            'format' => 'json',
            'limit' => 1,
        ];

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'FocusMap/1.0 (boubakrielaa4@email.com)'
            ])->withoutVerifying()->get('https://nominatim.openstreetmap.org/search', $params);

            // Log the full response for debugging
            Log::info('Nominatim API Response for lieu: ' . $objectif->lieu, [
                'status' => $response->status(),
                'body' => $response->body(),
                'json' => $response->json()
            ]);

            $data = $response->json();

            if ($response->successful() && is_array($data) && count($data) > 0) {
                $latitude = $data[0]['lat'];
                $longitude = $data[0]['lon'];
                $objectif->latitude = $latitude;
                $objectif->longitude = $longitude;
                $objectif->save();
                Log::info('Coordinates saved for ' . $objectif->lieu, [
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ]);
            } else {
                Log::warning('Nominatim: No results for lieu: ' . $objectif->lieu);
            }
        } catch (\Exception $e) {
            Log::error('Nominatim error: ' . $e->getMessage());
        }
    } else {
        $objectif->latitude = null;
        $objectif->longitude = null;
        $objectif->save();
        Log::info('No lieu provided for objective: ' . $objectif->titre);
    }
}

private function generateMindMapData($objectifs)
{
    $nodes = [];
    $nodes[] = ['id'=>'root', 'isroot'=>true, 'topic'=>'Mes Objectifs'];

    foreach ($objectifs as $obj) {
        $nodes[] = [
            'id' => 'obj_'.$obj->id,
            'parentid' => 'root',
            'topic' => $obj->titre
        ];
        foreach ($obj->etapes as $etape) {
            $nodes[] = [
                'id' => 'etape_'.$etape->id,
                'parentid' => 'obj_'.$obj->id,
                'topic' => $etape->titre
            ];
        }
    }

    return json_encode(['meta'=>['name'=>'mes_objectifs','author'=>'user','version'=>'1.0'], 'format'=>'node_tree', 'data'=>['id'=>'root','topic'=>'Mes Objectifs','children'=>[]], 'node_data'=>$nodes]);
}


}
