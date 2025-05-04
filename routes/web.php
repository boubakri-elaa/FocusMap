<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ObjectifController;

// Accueil
Route::get('/', [HomeController::class, 'index'])->name('home')->middleware('auth');;

// Inscription et Connexion
Route::get('/inscription', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/inscription', [AuthController::class, 'register'])->name('register.store');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');

Route::get('/userspace', [HomeController::class, 'userSpace'])->name('userspace')->middleware('auth');

Route::post('/objectifs', [ObjectifController::class, 'store'])->name('objectifs.store')->middleware('auth');
Route::delete('/objectifs/{objectif}', [ObjectifController::class, 'destroy'])->name('objectifs.destroy')->middleware('auth');


Route::get('/userspace', [ObjectifController::class, 'index'])->name('userspace')->middleware('auth');

Route::put('/objectifs/{objectif}', [ObjectifController::class, 'update'])->name('objectifs.update')->middleware('auth');
Route::delete('/objectifs/{objectif}', [ObjectifController::class, 'destroy'])->name('objectifs.destroy')->middleware('auth');
Route::post('/objectifs/suggest-steps', action: [ObjectifController::class, 'suggestSteps'])->name('objectifs.suggest-steps')->middleware('auth');
// Étapes : marquer comme complétée
Route::post('/etape/{id}/complete', [ObjectifController::class, 'completeEtape'])->name('etape.complete')->middleware('auth');