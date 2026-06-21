<?php

use App\Http\Controllers\Web\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('user.home.index');
});

Route::name('404-not-found')->get('404-not-found', function () {
    return view('404');
});

Route::get('/products', function () {
    return view('user.products.index');
});

Route::get('/products/premium-oversized-trench', function () {
    return view('user.products.show');
});

Route::get('/cart', function () {
    return view('user.cart.index');
});

Route::get('/checkout', function () {
    return view('user.checkout.index');
});


// Auth
Route::get('/login', [AuthController::class, 'index'])->name(name: 'auth.loginpage')->middleware('redirect.authenticated');
Route::post('/login', [AuthController::class, 'webLogin'])->name(name: 'auth.login');
Route::get('/logout', action: [AuthController::class, 'webLogout'])->name('auth.logout');