<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ImageController;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});


// Auth
Route::prefix('auth')->group(function () {
   
});

Route::middleware("auth:api")->group(function () {
    
});

?>