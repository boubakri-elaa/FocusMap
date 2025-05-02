<?php

namespace App\Http\Controllers;

use App\Models\User; // Import du modèle User
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
  
    public function showRegistrationForm(): View
    {
        return view('inscription');
    }
    public function showLoginForm(): View
{
    return view('login'); 
}


    public function register(Request $request)
    {
        // Validation des données du formulaire
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mot_de_passe' => 'required|string|min:8|confirmed', // 'confirmed' vérifie que password_confirmation correspond
        ]);

        // Création d'un nouvel utilisateur
        User::create([
            'name' => $request->nom,
            'email' => $request->email,
            'password' => Hash::make($request->mot_de_passe), // Hashage du mot de passe
        ]);

     
        return redirect()->route('login')->with('success', 'Compte créé avec succès ! Connecte-toi maintenant.');
    }
    public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (auth()->attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/userspace'); // ou une autre page protégée
    }

    return back()->withErrors([
        'email' => 'Email ou mot de passe incorrect.',
    ])->onlyInput('email');
}
public function logout(Request $request)
{
    auth()->logout(); 
    $request->session()->invalidate();
    $request->session()->regenerateToken(); // Regénère le token CSRF

    return redirect('/login'); 
}

}
