<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\ImageController;

$accountRoutes = function (string $accountType): void {
    Route::get('/', [UserController::class, 'index'])->name('list')->defaults('account_type', $accountType);
    Route::get('/trash', [UserController::class, 'trash'])->name('trash')->defaults('account_type', $accountType);
    Route::get('/create', [UserController::class, 'create'])->name('create')->defaults('account_type', $accountType);
    Route::post('/', [UserController::class, 'store'])->name('store')->defaults('account_type', $accountType);
    Route::get('/{id}', [UserController::class, 'show'])->name('show')->defaults('account_type', $accountType);
    Route::put('/{id}', [UserController::class, 'update'])->name('update')->defaults('account_type', $accountType);
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy')->defaults('account_type', $accountType);
    Route::patch('/{id}/restore', [UserController::class, 'restore'])->name('restore')->defaults('account_type', $accountType);
    Route::delete('/{id}/force-delete', [UserController::class, 'forceDelete'])->name('forceDelete')->defaults('account_type', $accountType);
};

Route::middleware('auth.login')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () use ($accountRoutes) {
        Route::prefix('customers')->name('customers.')->group(fn () => $accountRoutes('customer'));

        Route::middleware('admin')->group(function () use ($accountRoutes) {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

            Route::prefix('staff')->name('staff.')->group(fn () => $accountRoutes('staff'));
            Route::prefix('users')->name('users.')->group(fn () => $accountRoutes('all'));

            Route::prefix('images')->name('images.')->group(function () {
                Route::get('/', [ImageController::class, 'index'])->name('list');
                Route::delete('/{id}', [ImageController::class, 'destroy'])->name('destroy');
            });
        });
    });
