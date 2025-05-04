<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ObjectifController;
use App\Http\Controllers\Api\EtapeController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
