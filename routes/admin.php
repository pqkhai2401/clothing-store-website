<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GoodsReceiptController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\StockIssueController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;

$accountRoutes = function (string $accountType): void {
    Route::get('/', [UserController::class, 'index'])->name('list')->defaults('account_type', $accountType);
    Route::get('/create', [UserController::class, 'create'])->name('create')->defaults('account_type', $accountType);
    Route::post('/', [UserController::class, 'store'])->name('store')->defaults('account_type', $accountType);
    if ($accountType === 'customer') {
        Route::patch('/bulk-update-status', [UserController::class, 'bulkUpdateStatus'])->name('bulkUpdateStatus')->defaults('account_type', $accountType);
    } elseif ($accountType === 'all') {
        Route::get('/trash', [UserController::class, 'trash'])->name('trash')->defaults('account_type', $accountType);
        Route::post('/bulk-delete', [UserController::class, 'bulkDelete'])->name('bulkDelete')->defaults('account_type', $accountType);
        Route::patch('/{id}/restore', [UserController::class, 'restore'])->name('restore')->defaults('account_type', $accountType);
        Route::delete('/{id}/force-delete', [UserController::class, 'forceDelete'])->name('forceDelete')->defaults('account_type', $accountType);
    }
    Route::get('/{id}', [UserController::class, 'show'])->name('show')->defaults('account_type', $accountType);
    Route::put('/{id}', [UserController::class, 'update'])->name('update')->defaults('account_type', $accountType);
    if ($accountType !== 'staff') {
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy')->defaults('account_type', $accountType);
    }
};

$trashRoutes = function (string $controller): Closure {
    return function () use ($controller): void {
        Route::get('/trash', [$controller, 'trash'])->name('trash');
        Route::patch('/{id}/restore', [$controller, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [$controller, 'forceDelete'])->name('forceDelete');
    };
};

Route::middleware(['auth.login', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () use ($accountRoutes, $trashRoutes) {
        Route::prefix('customers')->name('customers.')->group(fn () => $accountRoutes('customer'));

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::middleware('permission:manage-staff')
            ->group(function () use ($accountRoutes) {
                Route::prefix('users')->name('staff.')->group(fn () => $accountRoutes('staff'));
            });

        Route::prefix('products')->name('products.')->group(function () use ($trashRoutes) {
            Route::get('/', [ProductController::class, 'index'])->name('list');
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ProductController::class, 'update'])->name('update');
            Route::patch('/{id}/toggle-status', [ProductController::class, 'toggleStatus'])->name('toggleStatus');
            Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-delete', [ProductController::class, 'bulkDelete'])->name('bulkDelete');
            Route::post('/trash/bulk-restore', [ProductController::class, 'bulkRestore'])->name('bulkRestore');
            Route::post('/trash/bulk-force-delete', [ProductController::class, 'bulkForceDelete'])->name('bulkForceDelete');
            $trashRoutes(ProductController::class)();
        });

        Route::prefix('categories')->name('categories.')->group(function () use ($trashRoutes) {
            Route::get('/', [CategoryController::class, 'index'])->name('list');
            Route::get('/{id}/edit', [CategoryController::class, 'edit'])->name('edit');
            Route::put('/{id}', [CategoryController::class, 'update'])->name('update');
            Route::patch('/{id}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('toggleStatus');
            Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('bulkDelete');
            Route::post('/trash/bulk-restore', [CategoryController::class, 'bulkRestore'])->name('bulkRestore');
            Route::post('/trash/bulk-force-delete', [CategoryController::class, 'bulkForceDelete'])->name('bulkForceDelete');
            $trashRoutes(CategoryController::class)();
        });

        Route::prefix('brands')->name('brands.')->group(function () use ($trashRoutes) {
            Route::get('/', [BrandController::class, 'index'])->name('list');
            Route::post('/', [BrandController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [BrandController::class, 'edit'])->name('edit');
            Route::put('/{id}', [BrandController::class, 'update'])->name('update');
            Route::patch('/{id}/toggle-status', [BrandController::class, 'toggleStatus'])->name('toggleStatus');
            Route::delete('/{id}', [BrandController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-delete', [BrandController::class, 'bulkDelete'])->name('bulkDelete');
            Route::post('/trash/bulk-restore', [BrandController::class, 'bulkRestore'])->name('bulkRestore');
            Route::post('/trash/bulk-force-delete', [BrandController::class, 'bulkForceDelete'])->name('bulkForceDelete');
            $trashRoutes(BrandController::class)();
        });

        Route::prefix('colors')->name('colors.')->group(function () use ($trashRoutes) {
            Route::get('/', [ColorController::class, 'index'])->name('list');
            Route::post('/', [ColorController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [ColorController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ColorController::class, 'update'])->name('update');
            Route::delete('/{id}', [ColorController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-delete', [ColorController::class, 'bulkDelete'])->name('bulkDelete');
            Route::post('/trash/bulk-restore', [ColorController::class, 'bulkRestore'])->name('bulkRestore');
            Route::post('/trash/bulk-force-delete', [ColorController::class, 'bulkForceDelete'])->name('bulkForceDelete');
            $trashRoutes(ColorController::class)();
        });

        Route::prefix('sizes')->name('sizes.')->group(function () use ($trashRoutes) {
            Route::get('/', [SizeController::class, 'index'])->name('list');
            Route::post('/', [SizeController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [SizeController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SizeController::class, 'update'])->name('update');
            Route::delete('/{id}', [SizeController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-delete', [SizeController::class, 'bulkDelete'])->name('bulkDelete');
            Route::post('/trash/bulk-restore', [SizeController::class, 'bulkRestore'])->name('bulkRestore');
            Route::post('/trash/bulk-force-delete', [SizeController::class, 'bulkForceDelete'])->name('bulkForceDelete');
            $trashRoutes(SizeController::class)();
        });

        Route::middleware('permission:manage-suppliers')
            ->prefix('suppliers')->name('suppliers.')->group(function () use ($trashRoutes) {
                Route::get('/', [SupplierController::class, 'index'])->name('list');
                Route::post('/', [SupplierController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [SupplierController::class, 'edit'])->name('edit');
                Route::put('/{id}', [SupplierController::class, 'update'])->name('update');
                Route::patch('/{id}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('toggleStatus');
                Route::delete('/{id}', [SupplierController::class, 'destroy'])->name('destroy');
                Route::post('/bulk-delete', [SupplierController::class, 'bulkDelete'])->name('bulkDelete');
                Route::post('/trash/bulk-restore', [SupplierController::class, 'bulkRestore'])->name('bulkRestore');
                Route::post('/trash/bulk-force-delete', [SupplierController::class, 'bulkForceDelete'])->name('bulkForceDelete');
                $trashRoutes(SupplierController::class)();
            });

        Route::middleware('permission:manage-goods-receipts')
            ->prefix('goods-receipts')->name('goods-receipts.')->group(function () {
                Route::get('/', [GoodsReceiptController::class, 'index'])->name('list');
                Route::get('/create', [GoodsReceiptController::class, 'create'])->name('create');
                Route::post('/', [GoodsReceiptController::class, 'store'])->name('store');
                Route::get('/{id}', [GoodsReceiptController::class, 'show'])->name('show');
                Route::patch('/{id}/complete', [GoodsReceiptController::class, 'complete'])->name('complete');
                Route::delete('/{id}', [GoodsReceiptController::class, 'destroy'])->name('destroy');
            });

        Route::middleware('permission:manage-goods-receipts')
            ->prefix('stock-issues')->name('stock-issues.')->group(function () use ($trashRoutes) {
                Route::get('/create', [StockIssueController::class, 'create'])->name('create');
                Route::post('/', [StockIssueController::class, 'store'])->name('store');
                Route::post('/bulk-delete', [StockIssueController::class, 'bulkDelete'])->name('bulkDelete');
                Route::post('/trash/bulk-restore', [StockIssueController::class, 'bulkRestore'])->name('bulkRestore');
                Route::post('/trash/bulk-force-delete', [StockIssueController::class, 'bulkForceDelete'])->name('bulkForceDelete');
                $trashRoutes(StockIssueController::class)();
                Route::get('/{id}', [StockIssueController::class, 'show'])->name('show');
                Route::patch('/{id}/issue', [StockIssueController::class, 'issue'])->name('issue');
                Route::delete('/{id}', [StockIssueController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('list');
            Route::get('/{id}/detail', [OrderController::class, 'detail'])->name('detail');
            Route::put('/{id}', [OrderController::class, 'update'])->name('update');
        });

        Route::prefix('reviews')->name('reviews.')->group(function () use ($trashRoutes) {
            Route::get('/', [ReviewController::class, 'index'])->name('list');
            Route::delete('/{id}', [ReviewController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-delete', [ReviewController::class, 'bulkDelete'])->name('bulkDelete');
            $trashRoutes(ReviewController::class)();
        });
    });
