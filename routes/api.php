<?php

use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});


// Auth
Route::prefix('auth')->group(function () {
   
});

Route::middleware("auth:api")->group(function () {
    
});
