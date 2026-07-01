<?php
use App\Http\Controllers\User\ProductController;
use App\Http\Controllers\User\WishlistController;
use App\Http\Controllers\User\AddressController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\ProfileController;

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('user.home.index');
})->name('home');

Route::name('404-not-found')->get('404-not-found', function () {
    return view('404');
});

Route::view('/about', 'user.about.index')->name('about');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/search', [ProductController::class, 'search'])->name('search');
Route::get('/api/search/suggestions', [ProductController::class, 'suggestions'])->name('search.suggestions');

// Route chi tiết sản phẩm: /san-pham/{slug}
Route::get('/san-pham/{slug}', [ProductController::class, 'show'])->name('products.show');

// Route danh mục sản phẩm: /danh-muc/{slug}?gender=men|women
Route::get('/danh-muc/{slug}', [ProductController::class, 'getProductsByCategory'])
    ->name('category.products');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'store'])->name('cart.add');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::patch('/cart/{cartItem}/variant', [CartController::class, 'switchVariant'])->name('cart.variant');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/user/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/user/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::delete('/user/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
});


// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
});

// Wishlist (yêu cầu đăng nhập)
Route::middleware('auth')->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/add/{productId}', [WishlistController::class, 'add'])->name('wishlist.add');
    Route::post('/wishlist/toggle/{productId}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/remove/{productId}', [WishlistController::class, 'remove'])->name('wishlist.remove');
    Route::delete('/wishlist/clear', [WishlistController::class, 'clear'])->name('wishlist.clear');
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