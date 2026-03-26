<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Console\Scheduling\Schedule;

use App\Models\Category;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        View::composer('*', function ($view) {
            $categories = Category::orderBy('name')->get();

            $view->with('navbarCategories', $categories);
        });

        $schedule = app(Schedule::class);

        $schedule->call(function () {
            Artisan::call('cart:clear-old');
            Log::info('Cart items vecchi eliminati tramite scheduler.');
        })->daily();
    }
}
