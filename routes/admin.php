<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GoodsReceiptController;
use App\Http\Controllers\Admin\InventoryReportController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\Settings\SettingController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\StockIssueController;
use App\Http\Controllers\Admin\StocktakeController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\ActivityLogController;

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
    Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit')->defaults('account_type', $accountType);
    Route::get('/{id}', [UserController::class, 'show'])->name('show')->defaults('account_type', $accountType);
    Route::put('/{id}', [UserController::class, 'update'])->name('update')->defaults('account_type', $accountType);
    Route::patch('/{id}/reset-password', [UserController::class, 'resetPassword'])->name('resetPassword')->defaults('account_type', $accountType);
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

Route::middleware(['auth.login', 'active.account', 'admin', 'force.password.change'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () use ($accountRoutes, $trashRoutes) {
        Route::middleware('permission:manage-customers')
            ->prefix('customers')->name('customers.')->group(fn () => $accountRoutes('customer'));

        Route::get('/', [DashboardController::class, 'index'])
            ->middleware('permission:view-dashboard')
            ->name('dashboard');

        Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::middleware('permission:manage-staff')
            ->group(function () use ($accountRoutes) {
                Route::prefix('users')->name('staff.')->group(fn () => $accountRoutes('staff'));
            });

        Route::prefix('products')->name('products.')->group(function () use ($trashRoutes) {
            Route::get('/', [ProductController::class, 'index'])->name('list');
            Route::get('/export', [ProductController::class, 'export'])->name('export');
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::post('/quick-create', [ProductController::class, 'quickCreate'])->name('quickCreate');
            Route::get('/search-similar', [ProductController::class, 'searchSimilar'])->name('searchSimilar');
            Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ProductController::class, 'update'])->name('update');
            Route::patch('/{id}/toggle-status', [ProductController::class, 'toggleStatus'])->name('toggleStatus');
            Route::patch('/{id}/toggle-featured', [ProductController::class, 'toggleFeatured'])->name('toggleFeatured');
            Route::patch('/{id}/quick-update', [ProductController::class, 'quickUpdate'])->name('quickUpdate');
            Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-delete', [ProductController::class, 'bulkDelete'])->name('bulkDelete');
            Route::post('/trash/bulk-restore', [ProductController::class, 'bulkRestore'])->name('bulkRestore');
            Route::post('/trash/bulk-force-delete', [ProductController::class, 'bulkForceDelete'])->name('bulkForceDelete');
            $trashRoutes(ProductController::class)();
        });

        Route::prefix('categories')->name('categories.')->group(function () use ($trashRoutes) {
            Route::get('/', [CategoryController::class, 'index'])->name('list');
            Route::post('/', [CategoryController::class, 'store'])->name('store');
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
            Route::patch('/{id}/toggle-status', [ColorController::class, 'toggleStatus'])->name('toggleStatus');
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
                Route::get('/reports/profit', [InventoryReportController::class, 'profit'])->name('reports.profit');
                Route::get('/stock-card/{variant}', [GoodsReceiptController::class, 'stockCard'])->name('stockCard');
                Route::post('/', [GoodsReceiptController::class, 'store'])->name('store');
                Route::post('/bulk-delete', [GoodsReceiptController::class, 'bulkDelete'])->name('bulkDelete');
                Route::post('/trash/bulk-restore', [GoodsReceiptController::class, 'bulkRestore'])->name('bulkRestore');
                Route::post('/trash/bulk-force-delete', [GoodsReceiptController::class, 'bulkForceDelete'])->name('bulkForceDelete');
                Route::get('/trash', [GoodsReceiptController::class, 'trash'])->name('trash');
                Route::get('/{id}/edit', [GoodsReceiptController::class, 'edit'])->name('edit');
                Route::put('/{id}', [GoodsReceiptController::class, 'update'])->name('update');
                Route::get('/{id}', [GoodsReceiptController::class, 'show'])->name('show');
                Route::patch('/{id}/complete', [GoodsReceiptController::class, 'complete'])->name('complete');
                Route::patch('/{id}/adjust', [GoodsReceiptController::class, 'adjust'])->name('adjust');
                Route::patch('/{id}/restore', [GoodsReceiptController::class, 'restore'])->name('restore');
                Route::delete('/{id}/force-delete', [GoodsReceiptController::class, 'forceDelete'])->name('forceDelete');
                Route::delete('/{id}', [GoodsReceiptController::class, 'destroy'])->name('destroy');
            });

        Route::middleware('permission:manage-goods-receipts')
            ->prefix('warehouses')->name('warehouses.')->group(function () {
                Route::post('/', [WarehouseController::class, 'store'])->name('store');
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
                Route::get('/{id}/edit', [StockIssueController::class, 'edit'])->name('edit');
                Route::put('/{id}', [StockIssueController::class, 'update'])->name('update');
                Route::patch('/{id}/issue', [StockIssueController::class, 'confirm'])->name('issue');
                Route::patch('/{id}/cancel', [StockIssueController::class, 'cancel'])->name('cancel');
                Route::delete('/{id}', [StockIssueController::class, 'destroy'])->name('destroy');
            });

        Route::middleware('permission:manage-goods-receipts')
            ->prefix('stocktakes')->name('stocktakes.')->group(function () {
                Route::post('/', [StocktakeController::class, 'store'])->name('store');
                Route::post('/trash/bulk-restore', [StocktakeController::class, 'bulkRestore'])->name('bulkRestore');
                Route::post('/trash/bulk-force-delete', [StocktakeController::class, 'bulkForceDelete'])->name('bulkForceDelete');
                Route::get('/trash', [StocktakeController::class, 'trash'])->name('trash');
                Route::get('/{id}', [StocktakeController::class, 'show'])->name('show');
                Route::patch('/{id}/approve', [StocktakeController::class, 'approve'])->name('approve');
                Route::patch('/{id}/reject', [StocktakeController::class, 'reject'])->name('reject');
                Route::patch('/{id}/restore', [StocktakeController::class, 'restore'])->name('restore');
                Route::delete('/{id}/force-delete', [StocktakeController::class, 'forceDelete'])->name('forceDelete');
                Route::delete('/{id}', [StocktakeController::class, 'destroy'])->name('destroy');
            });

        Route::middleware('permission:manage-orders')
            ->prefix('orders')->name('orders.')->group(function () use ($trashRoutes) {
            Route::get('/', [OrderController::class, 'index'])->name('list');
            Route::get('/export', [OrderController::class, 'export'])->name('export');
            Route::post('/bulk-update-status', [OrderController::class, 'bulkUpdateStatus'])->name('bulkUpdateStatus');
            Route::get('/create', [OrderController::class, 'create'])->name('create');
            Route::post('/', [OrderController::class, 'store'])->name('store');
            Route::get('/search-customers', [OrderController::class, 'searchCustomers'])->name('searchCustomers');
            Route::get('/search-variants', [OrderController::class, 'searchVariants'])->name('searchVariants');
            Route::get('/customers/{user}/addresses', [OrderController::class, 'customerAddresses'])->name('customerAddresses');
            Route::post('/trash/bulk-restore', [OrderController::class, 'bulkRestore'])->name('bulkRestore');
            Route::post('/trash/bulk-force-delete', [OrderController::class, 'bulkForceDelete'])->name('bulkForceDelete');
            $trashRoutes(OrderController::class)();
            Route::get('/{id}/detail', [OrderController::class, 'detail'])->name('detail');
            Route::get('/{id}/edit-content', [OrderController::class, 'editContent'])->name('editContent');
            Route::put('/{id}/edit-content', [OrderController::class, 'updateContent'])->name('updateContent');
            Route::put('/{id}', [OrderController::class, 'update'])->name('update');
            Route::delete('/{id}', [OrderController::class, 'destroy'])->name('destroy');
            });

        Route::middleware('permission:manage-reviews')
            ->prefix('reviews')->name('reviews.')->group(function () use ($trashRoutes) {
            Route::get('/', [ReviewController::class, 'index'])->name('list');
            // Duyệt / Từ chối thủ công (Admin kiểm duyệt tay các review flagged).
            Route::patch('/{id}/approve', [ReviewController::class, 'approve'])->name('approve');
            Route::patch('/{id}/reject', [ReviewController::class, 'reject'])->name('reject');
            Route::delete('/{id}', [ReviewController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-delete', [ReviewController::class, 'bulkDelete'])->name('bulkDelete');
            $trashRoutes(ReviewController::class)();
            });

        Route::middleware('permission:manage-vouchers')
            ->prefix('vouchers')->name('vouchers.')->group(function () {
                Route::get('/', [VoucherController::class, 'index'])->name('list');
                Route::get('/export', [VoucherController::class, 'export'])->name('export');
                Route::get('/trash', [VoucherController::class, 'trash'])->name('trash');
                Route::post('/trash/bulk-restore', [VoucherController::class, 'bulkRestore'])->name('bulkRestore');
                Route::post('/trash/bulk-force-delete', [VoucherController::class, 'bulkForceDelete'])->name('bulkForceDelete');
                Route::get('/create', [VoucherController::class, 'create'])->name('create');
                Route::post('/', [VoucherController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [VoucherController::class, 'edit'])->name('edit');
                Route::put('/{id}', [VoucherController::class, 'update'])->name('update');
                Route::patch('/{id}/toggle-status', [VoucherController::class, 'toggleStatus'])->name('toggleStatus');
                Route::patch('/{id}/restore', [VoucherController::class, 'restore'])->name('restore');
                Route::delete('/{id}/force-delete', [VoucherController::class, 'forceDelete'])->name('forceDelete');
                Route::delete('/{id}', [VoucherController::class, 'destroy'])->name('destroy');
                Route::post('/bulk-delete', [VoucherController::class, 'bulkDelete'])->name('bulkDelete');
            });

        Route::middleware('permission:manage-logs')
            ->prefix('logs')->name('logs.')->group(function () {
                Route::get('/', [ActivityLogController::class, 'index'])->name('list');
                Route::get('/export', [ActivityLogController::class, 'export'])->name('export');
                Route::post('/prune', [ActivityLogController::class, 'prune'])->name('prune');
                Route::post('/bulk-delete', [ActivityLogController::class, 'bulkDelete'])->name('bulkDelete');
                Route::get('/{id}', [ActivityLogController::class, 'show'])->name('show')->whereNumber('id');
            });

        Route::middleware('permission:manage-collections')
            ->prefix('collections')->name('collections.')->group(function () {
                Route::get('/', [CollectionController::class, 'index'])->name('list');
                Route::get('/create', [CollectionController::class, 'create'])->name('create');
                Route::post('/', [CollectionController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [CollectionController::class, 'edit'])->name('edit');
                Route::put('/{id}', [CollectionController::class, 'update'])->name('update');
                Route::delete('/{id}', [CollectionController::class, 'destroy'])->name('destroy');
            });

        Route::middleware('permission:manage-settings')
            ->prefix('settings')->name('settings.')->group(function () {
                Route::get('/', [SettingController::class, 'edit'])->name('edit');
                Route::put('/', [SettingController::class, 'update'])->name('update');
            });
    });
