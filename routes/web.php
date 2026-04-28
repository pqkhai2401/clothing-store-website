<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::name('404-not-found')->get('404-not-found', function () {
    return view('404');
});

// Auth
Route::get('/login', [AuthController::class, 'index'])->name(name: 'auth.loginpage')->middleware('redirect.authenticated');
Route::post('/login', [AuthController::class, 'login'])->name(name: 'auth.login');
Route::get('/logout', action: [AuthController::class, 'logout'])->name('auth.logout');

Route::middleware('admin')->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        // Users
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('list');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{id}', [UserController::class, 'update'])->name('update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
        });
        // Images
        Route::prefix('images')->name('images.')->group(function () {
            Route::get('/', [ImageController::class, 'index'])->name('list');
            Route::delete('/{id}', [ImageController::class, 'destroy'])->name('destroy');
        });
    });
});
