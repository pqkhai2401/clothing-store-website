<?php

use App\Http\Controllers\User\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// API kiểm tra biến thể sản phẩm (màu + size) để lấy tồn kho, SKU, giá
Route::post('/products/check-variant', [\App\Http\Controllers\User\ProductController::class, 'checkVariant']);

// API áp dụng mã giảm giá (voucher) cho giỏ hàng
Route::post('/vouchers/apply', [\App\Http\Controllers\Api\VoucherController::class, 'apply'])->name('api.vouchers.apply');


// Auth
Route::prefix('auth')->group(function () {
   
});

// Wishlist count — không cần auth middleware, controller tự kiểm tra Auth::check()
Route::get('/wishlist/count', [WishlistController::class, 'count'])->name('api.wishlist.count');