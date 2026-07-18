<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationActivity;
use App\Models\Cart;
use App\Models\Collection;
use App\Models\Wishlist;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Passport::enablePasswordGrant();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Ghi nhật ký đăng nhập / đăng xuất của nhân sự quản trị
        Event::listen(Login::class, [LogAuthenticationActivity::class, 'handleLogin']);
        Event::listen(Logout::class, [LogAuthenticationActivity::class, 'handleLogout']);

        // Cung cấp số lượng + danh sách id wishlist cho header và product-card
        View::composer(['partials.header', 'partials.product-card'], function ($view): void {
            if (Auth::check()) {
                $rows = Wishlist::where('user_id', Auth::id())
                    ->pluck('product_id')
                    ->all();
                $view->with('wishlistCount', count($rows));
                $view->with('userWishlistIds', $rows);
            } else {
                $view->with('wishlistCount', 0);
                $view->with('userWishlistIds', []);
            }
        });

        // Cung cấp số lượng sản phẩm trong giỏ hàng cho header
        View::composer('partials.header', function ($view): void {
            $cartCount = Auth::check()
                ? (int) (Cart::where('user_id', Auth::id())->first()?->cartItems()->sum('quantity') ?? 0)
                : 0;

            $view->with('cartCount', $cartCount);
        });

        // Cung cấp danh sách bộ sưu tập theo mùa cho dropdown trên header
        View::composer('partials.header', function ($view): void {
            $navCollections = Collection::where('status', true)
                ->orderBy('id')
                ->limit(8)
                ->get(['id', 'name', 'slug']);

            $view->with('navCollections', $navCollections);
        });

        // Cấu hình website (tên, logo, hotline...) dùng ở header/footer storefront,
        // sidebar admin và các phiếu in kho. Chia sẻ chung cho mọi view cần.
        View::composer([
            'partials.header',
            'partials.footer',
            'layouts.partial.sidebar',
            'admin.inventory.goods-receipts.partials.show-content',
            'admin.inventory.stock-issues.partials.show-content',
            'admin.inventory.goods-receipts.print',
            'admin.inventory.stock-issues.print',
        ], function ($view): void {
            $view->with('siteSettings', \App\Models\Setting::current());
        });

        // Danh sách banner trang chủ đang bật, dùng cho slider hero-section.
        View::composer('user.home.index', function ($view): void {
            $view->with('heroBanners', \App\Models\HeroBanner::where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get());
        });

        View::composer([
            'layouts.partial.sidebar',
        ], function ($view): void {
            $menu = [];

            if (Auth::check()) {
                $user = Auth::user();

                if (! $user->can('access-admin')) {
                    $view->with('menu', $menu);
                    return;
                }

                $r = fn (string $name, string $fallback): string => Route::has($name) ? route($name) : url($fallback);

                // Danh sách tất cả menu items, mỗi item gắn với 1 permission
                $allItems = [
                    [
                        'permission'     => 'view-dashboard',
                        'title'          => 'Dashboard',
                        'url'            => $r('admin.dashboard', '/admin'),
                        'active_pattern' => 'admin',
                        'icon'           => 'fa-solid fa-gauge',
                        'parent'         => [],
                    ],
                    [
                        'permission'     => 'manage-staff',
                        'title'          => 'Quản lý nhân sự',
                        'url'            => $r('admin.staff.list', '/admin/staff'),
                        'active_pattern' => 'admin/users*',
                        'icon'           => 'fa-solid fa-user-tie',
                        'parent'         => [],
                    ],
                    [
                        'permission'     => 'manage-customers',
                        'title'          => 'Quản lý khách hàng',
                        'url'            => $r('admin.customers.list', '/admin/customers'),
                        'active_pattern' => 'admin/customers*',
                        'icon'           => 'fa-solid fa-users',
                        'parent'         => [],
                    ],
                    [
                        'permission'     => 'manage-products',
                        'title'          => 'Quản lý sản phẩm',
                        'url'            => $r('admin.products.list', '/admin/products'),
                        'active_pattern' => 'admin/products*',
                        'icon'           => 'fa-solid fa-shirt',
                        'parent'         => [],
                    ],
                    [
                        'permission'     => 'manage-goods-receipts',
                        'title'          => 'Quản lý kho hàng',
                        'url'            => $r('admin.goods-receipts.list', '/admin/goods-receipts'),
                        'active_pattern' => 'admin/goods-receipts*',
                        'icon'           => 'fa-solid fa-box-open',
                        'parent'         => [],
                    ],
                    // [
                    //     'permission'     => 'manage-goods-receipts',
                    //     'title'          => 'Báo cáo lãi gộp (FIFO)',
                    //     'url'            => $r('admin.goods-receipts.reports.profit', '/admin/goods-receipts/reports/profit'),
                    //     'active_pattern' => 'admin/goods-receipts/reports*',
                    //     'icon'           => 'fa-solid fa-chart-line',
                    //     'parent'         => [],
                    // ],
                    [
                        'permission'     => 'manage-suppliers',
                        'title'          => 'Quản lý nhà cung cấp',
                        'url'            => $r('admin.suppliers.list', '/admin/suppliers'),
                        'active_pattern' => 'admin/suppliers*',
                        'icon'           => 'fa-solid fa-truck',
                        'parent'         => [],
                    ],
                    [
                        'permission'     => 'manage-categories',
                        'title'          => 'Quản lý danh mục',
                        'url'            => $r('admin.categories.list', '/admin/categories'),
                        'active_pattern' => 'admin/categories*',
                        'icon'           => 'fa-solid fa-layer-group',
                        'parent'         => [],
                    ],
                    [
                        'permission'     => 'manage-collections',
                        'title'          => 'Quản lý Bộ sưu tập',
                        'url'            => $r('admin.collections.list', '/admin/collections'),
                        'active_pattern' => 'admin/collections*',
                        'icon'           => 'fa-solid fa-umbrella-beach',
                        'parent'         => [],
                    ],
                    [
                        'permission'     => 'manage-brands',
                        'title'          => 'Quản lý thương hiệu',
                        'url'            => $r('admin.brands.list', '/admin/brands'),
                        'active_pattern' => 'admin/brands*',
                        'icon'           => 'fa-solid fa-tags',
                        'parent'         => [],
                    ],
                    [
                        'permission'     => 'manage-colors',
                        'title'          => 'Quản lý màu sắc',
                        'url'            => $r('admin.colors.list', '/admin/colors'),
                        'active_pattern' => 'admin/colors*',
                        'icon'           => 'fa-solid fa-palette',
                        'parent'         => [],
                    ],
                    [
                        'permission'     => 'manage-sizes',
                        'title'          => 'Quản lý kích thước',
                        'url'            => $r('admin.sizes.list', '/admin/sizes'),
                        'active_pattern' => 'admin/sizes*',
                        'icon'           => 'fa-solid fa-ruler-combined',
                        'parent'         => [],
                    ],
                    [
                        'permission'     => 'manage-orders',
                        'title'          => 'Quản lý đơn hàng',
                        'url'            => $r('admin.orders.list', '/admin/orders'),
                        'active_pattern' => 'admin/orders*',
                        'icon'           => 'fa-solid fa-receipt',
                        'parent'         => [],
                    ],
                    [
                        'permission'     => 'manage-reviews',
                        'title'          => 'Quản lý đánh giá',
                        'url'            => $r('admin.reviews.list', '/admin/reviews'),
                        'active_pattern' => 'admin/reviews*',
                        'icon'           => 'fa-solid fa-star',
                        'parent'         => [],
                    ],
                    [
                        'permission'     => 'manage-vouchers',
                        'title'          => 'Quản lý voucher',
                        'url'            => $r('admin.vouchers.list', '/admin/vouchers'),
                        'active_pattern' => 'admin/vouchers*',
                        'icon'           => 'fa-solid fa-ticket',
                        'parent'         => [],
                    ],
                    [
                        'permission'     => 'manage-revenue',
                        'title'          => 'Thống kê doanh thu',
                        'url'            => $r('admin.revenue.index', '/admin/revenue'),
                        'active_pattern' => 'admin/revenue*',
                        'icon'           => 'fa-solid fa-chart-line',
                        'parent'         => [],
                    ],
                    [
                        'permission'     => 'manage-logs',
                        'title'          => 'Nhật ký làm việc',
                        'url'            => $r('admin.logs.list', '/admin/logs'),
                        'active_pattern' => 'admin/logs*',
                        'icon'           => 'fa-solid fa-clock-rotate-left',
                        'parent'         => [],
                    ],
                    [
                        'permission'     => 'manage-settings',
                        'title'          => 'Cài đặt website',
                        'url'            => $r('admin.settings.edit', '/admin/settings'),
                        'active_pattern' => 'admin/settings*',
                        'icon'           => 'fa-solid fa-gear',
                        'parent'         => [],
                    ],
                    [
                        'permission'     => 'manage-settings',
                        'title'          => 'Banner trang chủ',
                        'url'            => $r('admin.hero-banners.list', '/admin/hero-banners'),
                        'active_pattern' => 'admin/hero-banners*',
                        'icon'           => 'fa-solid fa-images',
                        'parent'         => [],
                    ],
                ];

                // Chỉ hiển thị những menu item mà user có permission tương ứng
                $menu = array_values(array_filter(
                    $allItems,
                    fn (array $item) => $user->can($item['permission'])
                ));
            }

            $view->with('menu', $menu);
        });
    }
}