<?php

namespace LadyFauzia\Providers;

use Illuminate\Support\ServiceProvider;

class LadyFauziaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load database migrations
        $this->loadMigrationsFrom(dirname(__DIR__) . '/Database/Migrations');

        // Bind event listeners for loyalty engine
        \Illuminate\Support\Facades\Event::listen(
            'checkout.order.save.after',
            [\LadyFauzia\Listeners\OrderListener::class, 'handle']
        );

        \Illuminate\Support\Facades\Event::listen(
            'customer.registration.after',
            [\LadyFauzia\Listeners\CustomerListener::class, 'handle']
        );

        \Illuminate\Support\Facades\Event::listen(
            'customer.review.update.after',
            [\LadyFauzia\Listeners\ReviewListener::class, 'handle']
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                \LadyFauzia\Console\Commands\LadyFauziaTestLoyalty::class,
            ]);
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge custom settings with Bagisto's core settings
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/system.php',
            'core'
        );

        // Dynamically register Lady Fauzia API Resources in API Platform configuration
        $resources = config('api-platform.resources', []);
        
        $customPaths = [
            dirname(__DIR__) . '/Models/',
        ];

        foreach ($customPaths as $path) {
            if (is_dir($path) && ! in_array($path, $resources)) {
                $resources[] = $path;
            }
        }

        config(['api-platform.resources' => $resources]);
    }
}
