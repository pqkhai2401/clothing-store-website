<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\UserRepository;
use App\Repositories\Interfaces\UserRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        
    }

    public function boot() {}
}
