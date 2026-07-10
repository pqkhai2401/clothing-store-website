<?php
use App\Http\Controllers\User\ProductController;
use App\Http\Controllers\User\WishlistController;
use App\Http\Controllers\User\AddressController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\ForgotPasswordController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\PayosController;
use App\Http\Controllers\User\MomoController;
use App\Http\Controllers\User\LocationController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\ReviewController;

use App\Http\Controllers\User\HomeController;
use Illuminate\Support\Facades\Route;


Route::get('/', [HomeController::class, 'index'])->name('home');

Route::name('404-not-found')->get('404-not-found', function () {
    return view('404');
});

Route::view('/about', 'user.about.index')->name('about');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/new-arrivals', [ProductController::class, 'index'])->name('new-arrivals');
Route::get('/collections', [ProductController::class, 'index'])->name('collections');
Route::get('/search', [ProductController::class, 'search'])->name('search');
Route::get('/api/search/suggestions', [ProductController::class, 'suggestions'])->name('search.suggestions');

// Route proxy lấy dữ liệu Tỉnh/Thành (tránh lỗi CORS khi gọi trực tiếp API bên thứ ba)
Route::get('/api/location/provinces', [LocationController::class, 'provinces'])->name('location.provinces');

// Route chi tiết sản phẩm: /san-pham/{slug}
Route::get('/san-pham/{slug}', [ProductController::class, 'show'])->name('products.show');

// Route danh mục sản phẩm: /danh-muc/{slug}?gender=men|women
Route::get('/danh-muc/{slug}', [ProductController::class, 'getProductsByCategory'])
    ->name('category.products');

// Route bộ sưu tập mùa: /bo-suu-tap/{slug}
Route::get('/bo-suu-tap/{slug}', [ProductController::class, 'getProductsByCollection'])
    ->name('collections.show');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'store'])->name('cart.add');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::patch('/cart/{cartItem}/variant', [CartController::class, 'switchVariant'])->name('cart.variant');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // PayOS: trang QR nhúng + poll trạng thái + return/cancel (fallback từ trang hosted)
    Route::get('/checkout/payos/return', [PayosController::class, 'return'])->name('checkout.payos.return');
    Route::get('/checkout/payos/cancel', [PayosController::class, 'cancel'])->name('checkout.payos.cancel');
    Route::get('/checkout/payos/{order}', [PayosController::class, 'show'])->name('checkout.payos.show');
    Route::get('/checkout/payos/{order}/status', [PayosController::class, 'status'])->name('checkout.payos.status');

    // MoMo: trang QR nhúng + poll trạng thái + return (fallback từ app/trang hosted)
    Route::get('/checkout/momo-return', [MomoController::class, 'return'])->name('checkout.momo.return');
    Route::get('/checkout/momo/{order}', [MomoController::class, 'show'])->name('checkout.momo.show');
    Route::get('/checkout/momo/{order}/status', [MomoController::class, 'status'])->name('checkout.momo.status');

    Route::get('/user/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/user/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::delete('/user/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');

    // Gửi đánh giá sản phẩm (yêu cầu đăng nhập). {product} là ID sản phẩm.
    Route::post('/san-pham/{product}/danh-gia', [ReviewController::class, 'store'])->name('reviews.store');
});


// Orders
Route::middleware('auth')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}/detail', [OrderController::class, 'detail'])->name('orders.detail');
    Route::patch('/orders/{id}/cancel', [OrderController::class, 'cancelOrder'])->name('orders.cancel');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
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
// Quên mật khẩu (Forgot Password) - quy trình: Nhập Email -> Gửi OTP -> Xác thực OTP -> Đặt lại mật khẩu
Route::middleware('redirect.authenticated')->group(function () {
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('auth.password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('auth.password.send');

    Route::get('/verify-otp', [ForgotPasswordController::class, 'showVerifyForm'])->name('auth.password.verify');
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('auth.password.verify.submit');

    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('auth.password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('auth.password.update');
});

Route::get('/logout', action: [AuthController::class, 'webLogout'])->name('auth.logout');
