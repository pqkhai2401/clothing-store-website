<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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
        Passport::enablePasswordGrant();

        View::composer([
            'layouts.partial.sidebar',
        ], function ($view): void {
            $menu = [];

            if (Auth::check()) {
                $user = Auth::user();

                if ($user->isAdmin()) {
                    $adminUrl = fn (string $routeName, string $path): string => Route::has($routeName)
                        ? route($routeName)
                        : url($path);

                    $menu = [
                        [
                            'title' => 'Dashboard',
                            'url' => route('admin.dashboard'),
                            'active_pattern' => 'admin',
                            'icon' => 'fa-solid fa-gauge',
                            'parent' => [],
                        ],
                        [
                            'title' => 'Quản lý người dùng',
                            'url' => route('admin.users.list'),
                            'active_pattern' => 'admin/users*',
                            'icon' => 'fa-solid fa-users',
                            'parent' => [],
                        ],
                        [
                            'title' => 'Quản lý sản phẩm',
                            'url' => $adminUrl('admin.products.list', '/admin/products'),
                            'active_pattern' => 'admin/products*',
                            'icon' => 'fa-solid fa-shirt',
                            'parent' => [],
                        ],
                        [
                            'title' => 'Quản lý danh mục',
                            'url' => $adminUrl('admin.categories.list', '/admin/categories'),
                            'active_pattern' => 'admin/categories*',
                            'icon' => 'fa-solid fa-layer-group',
                            'parent' => [],
                        ],
                        [
                            'title' => 'Quản lý thương hiệu',
                            'url' => $adminUrl('admin.brands.list', '/admin/brands'),
                            'active_pattern' => 'admin/brands*',
                            'icon' => 'fa-solid fa-tags',
                            'parent' => [],
                        ],
                        [
                            'title' => 'Quản lý màu sắc',
                            'url' => $adminUrl('admin.colors.list', '/admin/colors'),
                            'active_pattern' => 'admin/colors*',
                            'icon' => 'fa-solid fa-palette',
                            'parent' => [],
                        ],
                        [
                            'title' => 'Quản lý kích thước',
                            'url' => $adminUrl('admin.sizes.list', '/admin/sizes'),
                            'active_pattern' => 'admin/sizes*',
                            'icon' => 'fa-solid fa-ruler-combined',
                            'parent' => [],
                        ],
                        [
                            'title' => 'Quản lý đơn hàng',
                            'url' => $adminUrl('admin.orders.list', '/admin/orders'),
                            'active_pattern' => 'admin/orders*',
                            'icon' => 'fa-solid fa-receipt',
                            'parent' => [],
                        ],
                        [
                            'title' => 'Quản lý đánh giá',
                            'url' => $adminUrl('admin.reviews.list', '/admin/reviews'),
                            'active_pattern' => 'admin/reviews*',
                            'icon' => 'fa-solid fa-star',
                            'parent' => [],
                        ],
                        [
                            'title' => 'Thống kê doanh thu',
                            'url' => $adminUrl('admin.revenue.index', '/admin/revenue'),
                            'active_pattern' => 'admin/revenue*',
                            'icon' => 'fa-solid fa-chart-line',
                            'parent' => [],
                        ],
                    ];
                }
            }

            $view->with('menu', $menu);
        });
    }
}
