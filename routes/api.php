<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ImageController;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});


// Auth
Route::prefix('auth')->group(function () {
    Route::post("/login", [AuthController::class, 'login'])->name("api.login");
    Route::post("/refresh-token", [AuthController::class, 'refreshToken'])->name("api.refresh-token");
    Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name("api.logout");
    Route::post("/request-forgot-password", [AuthController::class, 'requestForgotPassword']);
});

Route::middleware("auth:api")->group(function () {
    // Image
    Route::post('/upload-images', [ImageController::class, 'upload']);
    Route::get('/images', [ImageController::class, 'index']);
    Route::get('/images/{patientCode}', [ImageController::class, 'show']);
    Route::delete('/delete-image/{id}', [ImageController::class, 'destroy']);
});

?>
