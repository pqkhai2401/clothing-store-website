<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SizeController;
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

            Route::prefix('products')->name('products.')->group(function () {
                Route::get('/', [ProductController::class, 'index'])->name('list');
                Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('categories')->name('categories.')->group(function () {
                Route::get('/', [CategoryController::class, 'index'])->name('list');
                Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('brands')->name('brands.')->group(function () {
                Route::get('/', [BrandController::class, 'index'])->name('list');
                Route::delete('/{id}', [BrandController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('colors')->name('colors.')->group(function () {
                Route::get('/', [ColorController::class, 'index'])->name('list');
                Route::delete('/{id}', [ColorController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('sizes')->name('sizes.')->group(function () {
                Route::get('/', [SizeController::class, 'index'])->name('list');
                Route::delete('/{id}', [SizeController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('orders')->name('orders.')->group(function () {
                Route::get('/', [OrderController::class, 'index'])->name('list');
                Route::get('/{id}', [OrderController::class, 'show'])->name('show');
            });

            Route::prefix('reviews')->name('reviews.')->group(function () {
                Route::get('/', [ReviewController::class, 'index'])->name('list');
                Route::delete('/{id}', [ReviewController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('images')->name('images.')->group(function () {
                Route::get('/', [ImageController::class, 'index'])->name('list');
                Route::delete('/{id}', [ImageController::class, 'destroy'])->name('destroy');
            });
        });
    });
