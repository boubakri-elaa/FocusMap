<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use Illuminate\View\View;

class HomeController extends Controller
{
    
    public function index(): View
    {
        return view('index'); // Assurez-vous que le nom de la vue correspond à ton fichier (index.blade.php)
    }

    public function userSpace()
    {
        // Assure-toi que l'utilisateur est authentifié
        if (Auth::check()) {
            // Tu peux maintenant accéder aux objectifs de l'utilisateur connecté
            $user = Auth::user(); 
            return view('userspace', ['user' => $user]);
        }

        // Si l'utilisateur n'est pas authentifié, redirige-le vers la page de connexion
        return redirect()->route('login');
    }
    

}