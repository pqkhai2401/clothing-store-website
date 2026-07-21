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

Route::view('/gioi-thieu', 'user.about.index')->name('about');
Route::view('/dieu-khoan-dich-vu', 'user.terms.index')->name('terms');
Route::view('/chinh-sach-bao-mat', 'user.privacy.index')->name('privacy');
Route::view('/chinh-sach-doi-tra', 'user.returns.index')->name('returns');
Route::view('/cau-hoi-thuong-gap', 'user.faq.index')->name('faq');

Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::get('/hang-moi-ve', [ProductController::class, 'index'])->name('new-arrivals');
Route::get('/noi-bat', [ProductController::class, 'index'])->name('collections');
Route::get('/tim-kiem', [ProductController::class, 'search'])->name('search');
Route::get('/api/search/suggestions', [ProductController::class, 'suggestions'])->name('search.suggestions');

// Route proxy lấy dữ liệu Tỉnh/Thành (tránh lỗi CORS khi gọi trực tiếp API bên thứ ba)
Route::get('/api/location/provinces', [LocationController::class, 'provinces'])->name('location.provinces');
Route::get('/api/location/provinces/{code}/wards', [LocationController::class, 'wards'])
    ->whereNumber('code')->name('location.wards');

// Route chi tiết sản phẩm: /san-pham/{slug}
Route::get('/san-pham/{slug}', [ProductController::class, 'show'])->name('products.show');

// Route danh mục sản phẩm: /danh-muc/{slug}?gender=men|women
Route::get('/danh-muc/{slug}', [ProductController::class, 'getProductsByCategory'])
    ->name('category.products');

// Route bộ sưu tập mùa: /bo-suu-tap/{slug}
Route::get('/bo-suu-tap/{slug}', [ProductController::class, 'getProductsByCollection'])
    ->name('collections.show');

Route::middleware(['auth', 'active.account', 'auth.session'])->group(function () {
    Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/gio-hang/them', [CartController::class, 'store'])->name('cart.add');
    Route::patch('/gio-hang/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::patch('/gio-hang/{cartItem}/variant', [CartController::class, 'switchVariant'])->name('cart.variant');
    Route::delete('/gio-hang/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/thanh-toan', [CheckoutController::class, 'index'])->name('checkout.index');
    // throttle:10,1 — chống spam đặt hàng (mỗi lần tạo đơn giữ kho + gọi API cổng thanh toán,
    // spam liên tục không làm sai dữ liệu (mọi bước đều atomic/idempotent) nhưng lãng phí
    // tài nguyên và làm bẩn lịch sử giao dịch trên PayOS/MoMo).
    Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store')
        ->middleware('throttle:10,1');
    Route::post('/thanh-toan/{order}/doi-phuong-thuc', [CheckoutController::class, 'changePaymentMethod'])->name('checkout.changePaymentMethod')
        ->middleware('throttle:10,1');

    // PayOS: trang QR nhúng + poll trạng thái + return/cancel (fallback từ trang hosted)
    Route::get('/checkout/payos/return', [PayosController::class, 'return'])->name('checkout.payos.return');
    Route::get('/checkout/payos/cancel', [PayosController::class, 'cancel'])->name('checkout.payos.cancel');
    Route::get('/checkout/payos/{order}', [PayosController::class, 'show'])->name('checkout.payos.show');
    Route::get('/checkout/payos/{order}/status', [PayosController::class, 'status'])->name('checkout.payos.status');

    // MoMo: trang QR nhúng + poll trạng thái + return (fallback từ app/trang hosted)
    Route::get('/checkout/momo-return', [MomoController::class, 'return'])->name('checkout.momo.return');
    Route::get('/checkout/momo/{order}', [MomoController::class, 'show'])->name('checkout.momo.show');
    Route::get('/checkout/momo/{order}/status', [MomoController::class, 'status'])->name('checkout.momo.status');

    Route::get('/tai-khoan/dia-chi', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/tai-khoan/dia-chi', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/tai-khoan/dia-chi/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/tai-khoan/dia-chi/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');

    // Gửi đánh giá sản phẩm (yêu cầu đăng nhập). {product} là ID sản phẩm.
    // throttle:3,1 — tối đa 3 đánh giá/phút mỗi user để chống spam review.
    Route::post('/san-pham/{product}/danh-gia', [ReviewController::class, 'store'])
        ->middleware('throttle:3,1')
        ->name('reviews.store');
});


// Orders
Route::middleware(['auth', 'active.account', 'auth.session'])->group(function () {
    Route::get('/don-hang', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/don-hang/{id}/chi-tiet', [OrderController::class, 'detail'])->name('orders.detail');
    Route::patch('/don-hang/{id}/huy', [OrderController::class, 'cancelOrder'])->name('orders.cancel');
    Route::post('/don-hang/{id}/yeu-cau-huy', [OrderController::class, 'requestCancel'])->name('orders.requestCancel');
    Route::get('/don-hang/{id}', [OrderController::class, 'show'])->name('orders.show');
});

// Profile
Route::middleware(['auth', 'active.account', 'auth.session'])->group(function () {
    Route::get('/tai-khoan', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/tai-khoan', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/tai-khoan/doi-mat-khau', [ProfileController::class, 'changePassword'])->name('profile.change-password');
});

// Wishlist (yêu cầu đăng nhập)
Route::middleware(['auth', 'active.account', 'auth.session'])->group(function () {
    Route::get('/yeu-thich', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/yeu-thich/them/{productId}', [WishlistController::class, 'add'])->name('wishlist.add');
    Route::post('/yeu-thich/bat-tat/{productId}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/yeu-thich/xoa/{productId}', [WishlistController::class, 'remove'])->name('wishlist.remove');
    Route::delete('/yeu-thich/xoa-tat-ca', [WishlistController::class, 'clear'])->name('wishlist.clear');
});

// Auth
Route::get('/dang-nhap', [AuthController::class, 'index'])->name(name: 'auth.loginpage')->middleware('redirect.authenticated');
Route::post('/dang-nhap', [AuthController::class, 'login'])->name(name: 'auth.login')
    ->middleware('throttle:5,1');
Route::get('/dang-ky', [AuthController::class, 'registerPage'])->name('auth.registerpage')->middleware('redirect.authenticated');
// throttle:5,1 — chống script tạo hàng loạt tài khoản (bypass throttle theo-tài-khoản ở checkout
// bằng cách tạo nhiều tài khoản, mỗi tài khoản giữ 1 đơn online để "khóa" tồn kho khan hiếm).
Route::post('/dang-ky', [AuthController::class, 'webRegister'])->name('auth.register')
    ->middleware('throttle:5,1');
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])
    ->name('auth.google.redirect')
    ->middleware('redirect.authenticated');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
// Quên mật khẩu (Forgot Password) - quy trình: Nhập Email -> Gửi OTP -> Xác thực OTP -> Đặt lại mật khẩu
Route::middleware('redirect.authenticated')->group(function () {
    Route::get('/quen-mat-khau', [ForgotPasswordController::class, 'showForgotForm'])->name('auth.password.request');
    Route::post('/quen-mat-khau', [ForgotPasswordController::class, 'sendOtp'])->name('auth.password.send')
        ->middleware('throttle:5,1');

    Route::get('/xac-thuc-otp', [ForgotPasswordController::class, 'showVerifyForm'])->name('auth.password.verify');
    Route::post('/xac-thuc-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('auth.password.verify.submit')
        ->middleware('throttle:6,1');

    Route::get('/dat-lai-mat-khau', [ForgotPasswordController::class, 'showResetForm'])->name('auth.password.reset');
    Route::post('/dat-lai-mat-khau', [ForgotPasswordController::class, 'resetPassword'])->name('auth.password.update')
        ->middleware('throttle:6,1');
});

Route::post('/dang-xuat', action: [AuthController::class, 'logout'])->name('auth.logout');
