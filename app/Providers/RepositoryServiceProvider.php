<?php

namespace App\Providers;

use App\Interfaces\CategoryRepoInterface;
use App\Interfaces\OrderRepoInterface;
use App\Interfaces\ProductRepoInterface;
use App\Interfaces\TableRepoInterface;
use App\Interfaces\UserRepoInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\TableRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepoInterface::class, ProductRepository::class);
        $this->app->bind(CategoryRepoInterface::class, CategoryRepository::class);
        $this->app->bind(TableRepoInterface::class, TableRepository::class);
        $this->app->bind(UserRepoInterface::class, UserRepository::class);
        $this->app->bind(OrderRepoInterface::class, OrderRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
