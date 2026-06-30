<?php

use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// API kiểm tra biến thể sản phẩm (màu + size) để lấy tồn kho, SKU, giá
Route::post('/products/check-variant', [\App\Http\Controllers\Web\ProductController::class, 'checkVariant']);


// Auth
Route::prefix('auth')->group(function () {
   
});

Route::middleware("auth:api")->group(function () {
    
});
