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
                        'active_pattern' => 'admin/staff*',
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
                        'permission'     => 'manage-categories',
                        'title'          => 'Quản lý danh mục',
                        'url'            => $r('admin.categories.list', '/admin/categories'),
                        'active_pattern' => 'admin/categories*',
                        'icon'           => 'fa-solid fa-layer-group',
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
                        'permission'     => 'manage-revenue',
                        'title'          => 'Thống kê doanh thu',
                        'url'            => $r('admin.revenue.index', '/admin/revenue'),
                        'active_pattern' => 'admin/revenue*',
                        'icon'           => 'fa-solid fa-chart-line',
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
