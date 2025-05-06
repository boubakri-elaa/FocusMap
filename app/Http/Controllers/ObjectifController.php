<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Objectif;
use App\Models\Etape;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Comment;

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
        'etapes.*.titre' => 'nullable|string|max:255',
        'etapes.*.description' => 'nullable|string',
        'visibility' => 'required|in:public,private,friends',
    ]);

    try {
        $objectif = Objectif::create([
            'user_id' => Auth::id(),
            'titre' => $request->titre,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'lieu' => $request->lieu,
            'type' => $request->type,
            'visibility' =>$request->visibility,
        ]);

        if ($request->has('etapes')) {
            foreach ($request->etapes as $etapeData) {
                if (!empty($etapeData['titre'])) {
                    Etape::create([
                        'objectif_id' => $objectif->id,
                        'titre' => $etapeData['titre'],
                        'description' => $etapeData['description'] ?? '',
                    ]);
                }
            }
        }

        $this->geocodeAndSaveCoordinates($objectif);

        return response()->json([
            'success' => true,
            'message' => 'Objectif ajouté avec succès !',
            'objectif' => $objectif,
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Une erreur est survenue : ' . $e->getMessage(),
        ], 500);
    }
}

    public function update(Request $request, Objectif $objectif)
    {
        // Only allow the owner to update
        if ($objectif->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required',
            'deadline' => 'nullable|date',
            'lieu' => 'nullable|string|max:255',
            'type' => 'required|string',
            'etapes.*.titre' => 'nullable|string|max:255',
            'etapes.*.description' => 'nullable|string',
            'visibility' => 'required|in:public,private,friends',
        ]);

        $objectif->update([
            'titre' => $request->titre,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'lieu' => $request->lieu,
            'type' => $request->type,
            'visibility' => $request->visibility,
        ]);

        if ($request->has('etapes')) {
            $objectif->etapes()->delete();
            foreach ($request->etapes as $etapeData) {
                if (!empty($etapeData['titre'])) {
                    $objectif->etapes()->create([
                        'titre' => $etapeData['titre'],
                        'description' => $etapeData['description'] ?? '',
                    ]);
                }
            }
        }

        if ($request->filled('lieu') && $objectif->isDirty('lieu')) {
            $this->geocodeAndSaveCoordinates($objectif);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Objectif $objectif)
    {
        if ($objectif->user_id === Auth::id()) {
            $objectif->etapes()->delete();
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
        $nodes[] = ['id' => 'root', 'isroot' => true, 'topic' => '🎯 Mes Objectifs'];

        foreach ($objectifs as $obj) {
            $nodes[] = [
                'id' => 'obj_' . $obj->id,
                'parentid' => 'root',
                'topic' => $obj->titre
            ];
            foreach ($obj->etapes as $etape) {
                $nodes[] = [
                    'id' => 'etape_' . $etape->id,
                    'parentid' => 'obj_' . $obj->id,
                    'topic' => $etape->titre
                ];
            }
        }

        return json_encode([
            'meta' => ['name' => 'objectif_mindmap', 'author' => 'user', 'version' => '1.0'],
            'format' => 'node_array',
            'data' => $nodes
        ]);
    }

    public function suggestSteps(Request $request)
    {
        Log::info('suggestSteps called', ['titre' => $request->titre, 'input' => $request->all()]);
        $request->validate([
            'titre' => 'required|string|max:255',
        ], [
            'titre.required' => 'Le titre de l’objectif est requis.',
            'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'visibility' => 'required|in:public,private,friends',
        ]);

        try {
            $steps = $this->generateStepsWithGemini($request->titre);
            Log::info('Steps generated', ['steps' => $steps]);
            return response()->json([
                'success' => true,
                'steps' => $steps,
                'titre' => $request->titre,
                'message' => 'Étapes suggérées avec succès !'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération des étapes pour l\'objectif : ' . $request->titre . ' - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Impossible de générer les étapes : ' . $e->getMessage(),
                'titre' => $request->titre
            ], 500);
        }
    }
protected function generateStepsWithGemini($objectifTitle)
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            throw new \Exception('Clé API Gemini manquante dans .env');
        }

        $prompt = "Génère une liste de 3 à 5 étapes concrètes pour atteindre l'objectif : \"$objectifTitle\". Chaque étape doit être une phrase claire et actionnable,pas trop longue et formatée comme une liste numérotée (ex. 1. Faire X).";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->withOptions([
                'verify' => false, // Remove after adding CA bundle
            ])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 200,
                    'temperature' => 0.7,
                ],
            ]);

            if ($response->failed()) {
                throw new \Exception('Échec de la requête Gemini : ' . $response->body());
            }

            $data = $response->json();
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Réponse Gemini invalide ou vide');
            }

            $steps = $data['candidates'][0]['content']['parts'][0]['text'];
            $stepsArray = array_filter(array_map('trim', preg_split("/\n|\d+\.\s*/", $steps)), function ($step) {
                return !empty($step) && !preg_match('/^\d+\.$/', $step);
            });

            if (empty($stepsArray)) {
                throw new \Exception('Aucune étape valide générée par Gemini');
            }

            return array_values($stepsArray);
        } catch (\Exception $e) {
            Log::error('Erreur Gemini pour l\'objectif : ' . $objectifTitle . ' - ' . $e->getMessage());
            throw $e;
        }
    }


private function checkAndAssignBadge(Objectif $objectif)
{
    $user = $objectif->user;

    // Vérifier le nombre total d'étapes et le nombre d'étapes complètes
    $etapesTotal = $objectif->etapes->count();
    $etapesComplètes = $objectif->etapes->whereIn('id', $user->completedEtapes->pluck('id'))->count();

    // Si toutes les étapes sont complétées, attribuer un badge
    if ($etapesTotal > 0 && $etapesComplètes === $etapesTotal) {
        // Récupère un badge spécifique
        $badge = Badge::where('name', 'Objectif accompli')->first();

        // Vérifier si le badge existe et si l'utilisateur ne l'a pas déjà
        if ($badge && !$user->badges->contains($badge->id)) {
            $user->badges()->attach($badge->id);
        }
    }
}


public function completeEtape(Request $request, $id)
{
    $etape = Etape::findOrFail($id);
    $user = auth()->user();

    // Vérifie si ce n’est pas déjà fait
    $isCompleted = !$user->completedEtapes->contains($etape->id);
    if ($isCompleted) {
        $user->completedEtapes()->attach($etape->id);
    }

    return response()->json([
        'success' => true,
        'completed' => $isCompleted,
        'message' => $isCompleted ? 'Étape marquée comme complétée.' : 'Étape déjà complétée.'
    ]);
}

//Les objectifs public/amis
public function getSharedObjectifs()
    {
        $user = Auth::user();
        $objectifs = Objectif::where('visibility', 'public')
            ->orWhere(function ($query) use ($user) {
                $query->where('visibility', 'friends')
                      ->whereIn('user_id', $user->friends()->pluck('users.id'));
            })
            ->with('user')
            ->get();

        return response()->json($objectifs);
    }
    //Gestion des commentaires 
    public function storeComment(Request $request, $objectifId)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $comment = Comment::create([
            'objectif_id' => $objectifId,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return response()->json([
            'message' => 'Commentaire ajouté',
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'user' => ['name' => Auth::user()->name],
                'created_at' => $comment->created_at->diffForHumans(),
            ]
        ]);
    }

    public function getComments($objectifId)
    {
        $comments = Comment::where('objectif_id', $objectifId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => ['name' => $comment->user->name],
                    'created_at' => $comment->created_at->diffForHumans(),
                ];
            });

        return response()->json($comments);
    }
}
