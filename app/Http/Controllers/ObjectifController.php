<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Objectif;
use App\Models\Etape;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        'etapes.*.titre' => 'nullable|string|max:255',
        'etapes.*.description' => 'nullable|string',
    ]);

    try {
        $objectif = Objectif::create([
            'user_id' => Auth::id(),
            'titre' => $request->titre,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'lieu' => $request->lieu,
            'type' => $request->type,
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
        ]);

        $objectif->update([
            'titre' => $request->titre,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'lieu' => $request->lieu,
            'type' => $request->type,
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
            'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.'
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

        $prompt = "Génère une liste de 3 à 5 étapes concrètes pour atteindre l'objectif : \"$objectifTitle\". Chaque étape doit être une phrase claire et actionnable, formatée comme une liste numérotée (ex. 1. Faire X).";

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
/*  protected function generateStepsWithOpenAI($objectifTitle)
{
    // Mock response for testing
    $mockSteps = [
        "1. Rechercher des ressources sur {$objectifTitle}.",
        "2. Planifier un calendrier pour {$objectifTitle}.",
        "3. Pratiquer régulièrement pour maîtriser {$objectifTitle}.",
        "4. Évaluer les progrès chaque semaine."
    ];
    return $mockSteps;*/
    /*
    $apiKey = env('OPENAI_API_KEY');
    if (!$apiKey) {
        throw new \Exception('Clé API OpenAI manquante dans .env');
    }

    $prompt = "Génère une liste de 3 à 5 étapes concrètes pour atteindre l'objectif : \"$objectifTitle\". Chaque étape doit être une phrase claire et actionnable, formatée comme une liste numérotée (ex. 1. Faire X).";

    try {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])
        ->withOptions([
            'verify' => false, // Temporarily disable SSL verification (development only)
        ])
        ->timeout(15)
        ->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'Tu es un assistant qui génère des étapes claires et concises pour atteindre des objectifs.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => 200,
            'temperature' => 0.7,
        ]);

        if ($response->failed()) {
            throw new \Exception('Échec de la requête OpenAI : ' . $response->body());
        }

        $data = $response->json();
        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \Exception('Réponse OpenAI invalide ou vide');
        }

        $steps = $data['choices'][0]['message']['content'];
        $stepsArray = array_filter(array_map('trim', preg_split("/\n|\d+\.\s*/ /* ", $steps)), function ($step) {
            return !empty($step) && !preg_match('/^\d+\.$/', $step);
        });  

        if (empty($stepsArray)) {
            throw new \Exception('Aucune étape valide générée par OpenAI');
        }

        return array_values($stepsArray);
    } catch (\Exception $e) {
        Log::error('Erreur OpenAI pour l\'objectif : ' . $objectifTitle . ' - ' . $e->getMessage());
        throw $e;
    }
} */
 }