<?php

namespace Ikechukwukalu\Tokenmiddleware;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class TokenmiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Route::middleware('api')->prefix('api')->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        });

        $this->loadMigrationsFrom(__DIR__.'/migrations');
        $this->loadTranslationsFrom(__DIR__.'/lang', 'tokenmiddleware');

        $this->publishes([
            __DIR__.'/config' => base_path('config'),
        ], 'tm-config');
        $this->publishes([
            __DIR__.'/lang' => base_path('resources/lang/ikechukwukalu/tokenmiddleware'),
        ], 'tm-lang');
        $this->publishes([
            __DIR__.'/Controllers' => base_path('app/Http/Controllers/ikechukwukalu/tokenmiddleware'),
        ], 'tm-controllers');
        $this->publishes([
            __DIR__.'/Models' => base_path('app/Models/ikechukwukalu/tokenmiddleware'),
        ], 'tm-models');
        $this->publishes([
            __DIR__.'/Middleware' => base_path('app/Models/ikechukwukalu/tokenmiddleware'),
        ], 'tm-middleware');
        $this->publishes([
            __DIR__.'/Rules' => base_path('app/Models/ikechukwukalu/tokenmiddleware'),
        ], 'tm-rules');
        $this->publishes([
            __DIR__.'/Tests/Unit' => base_path('tests/Unit/ikechukwukalu/tokenmiddleware'),
        ], 'tm-unit-tests');
        $this->publishes([
            __DIR__.'/Tests/Feature' => base_path('tests/Feature/ikechukwukalu/tokenmiddleware'),
        ], 'tm-feature-tests');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/tokenmiddleware.php', 'tokenmiddleware'
        );

        $this->app->make(\Ikechukwukalu\Tokenmiddleware\Controllers\TokenController::class);
        $this->app->make(\Ikechukwukalu\Tokenmiddleware\Controllers\NovelController::class);
    }
}
