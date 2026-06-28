<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('user.home.index');
});

Route::name('404-not-found')->get('404-not-found', function () {
    return view('404');
});

Route::get('/products', [ProductController::class, 'index'])->name('products.index');

Route::get('/products/premium-oversized-trench', function () {
    return view('user.products.show');
});

// Route danh mục sản phẩm: /danh-muc/{slug}?gender=men|women
Route::get('/danh-muc/{slug}', [ProductController::class, 'getProductsByCategory'])
    ->name('category.products');

Route::get('/cart', function () {
    return view('user.cart.index');
});

Route::get('/checkout', function () {
    return view('user.checkout.index');
});


// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
});

// Auth
Route::get('/login', [AuthController::class, 'index'])->name(name: 'auth.loginpage')->middleware('redirect.authenticated');
Route::post('/login', [AuthController::class, 'webLogin'])->name(name: 'auth.login');
Route::get('/register', [AuthController::class, 'registerPage'])->name('auth.registerpage')->middleware('redirect.authenticated');
Route::post('/register', [AuthController::class, 'webRegister'])->name('auth.register');
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])
    ->name('auth.google.redirect')
    ->middleware('redirect.authenticated');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::view('/forgot-password', 'auth.forgot-password')
    ->name('auth.password.request')
    ->middleware('redirect.authenticated');
Route::get('/logout', action: [AuthController::class, 'webLogout'])->name('auth.logout');
