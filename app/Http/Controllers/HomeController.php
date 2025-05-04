<?php



namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Badge; // N'oubliez pas d'importer le modèle Badge
use App\Models;
use Illuminate\Http\Request; // Ajoutez cette ligne
use App\Models\User;



class HomeController extends Controller
{
    public function index(): View
    {
        $progression = 0;

        if (auth()->check()) {
            /** @var \App\Models\User $user */
            $user = auth()->user();
            $progression = $user->getCompletionPercentage();
        }

        return view('index', compact('progression'));
    }

    public function userSpace()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user()->load([
            'objectifs.etapes',
            'badges',
            'completedEtapes',
        ]);

        $completionPercentage = $user->getCompletionPercentage();

        return view('userspace', [
            'user' => $user,
            'objectifs' => $user->objectifs,
            'completionPercentage' => $completionPercentage,
            'allBadges' => Badge::all(),
            'userBadges' => $user->badges->pluck('id')->toArray(),
        ]);
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'titre' => 'required|string|max:255',
        'description' => 'required|string',
        'deadline' => 'nullable|date',
        'lieu' => 'nullable|string',
        'type' => 'required|string'
    ]);

    $objectif = Auth::user()->objectifs()->create($validated);

    // Gestion des étapes
    if ($request->has('etapes')) {
        foreach ($request->etapes as $etape) {
            $objectif->etapes()->create([
                'titre' => $etape['titre'],
                'description' => $etape['description']
            ]);
        }
    }

    return redirect()->route('userspace'); // Redirection vers l'espace utilisateur
}
}