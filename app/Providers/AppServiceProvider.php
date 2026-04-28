<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

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

        // Pass data to all sidebars
        View::composer([
            'layouts.partial.sidebar',
        ], function ($view) {

            if (Auth::check()) {
                $menu = [];

                $user = Auth::user();

                // Menu for Admin
                if ($user->role === 'admin') {
                    $menu = [
                        [
                            "title" => "Dashboard",
                            "url" => route('admin.dashboard'),
                            "active_pattern" => "admin",
                            "icon" => "fa-solid fa-gauge",
                            "parent" => []
                        ],
                        [
                            "title" => "Quản lý người dùng",
                            "url" => route('admin.users.list'),
                            "active_pattern" => "admin/users",
                            "icon" => "fa-solid fa-users",
                            "parent" => []
                        ],
                        [
                            "title" => "Ảnh bệnh nhân",
                            "url" => route('admin.images.list'),
                            "active_pattern" => "admin/images",
                            "icon" => "fa-solid fa-image",
                            "parent" => []
                        ],
                    ];
                }

                $view->with('menu', $menu);
            }
        });
    }
}
