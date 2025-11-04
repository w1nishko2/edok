<?php

namespace App\Providers;

use App\Models\Recipe;
use App\Observers\RecipeObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        // Регистрируем Observer для автоматического обновления sitemap
        Recipe::observe(RecipeObserver::class);
        
        // Принудительно используем HTTPS в продакшене
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
