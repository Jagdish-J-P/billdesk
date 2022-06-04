<?php

namespace JagdishJP\Billdesk;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use JagdishJP\Billdesk\Console\Commands\BilldeskPublish;
use JagdishJP\Billdesk\Console\Commands\TransactionStatus;

class BilldeskServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'billdesk');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRoutes();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'billdesk');

        $this->configurePublish();
    }

    public function configureRoutes()
    {
        Route::group([
            'middleware' => Config::get('billdesk.middleware'),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    public function configurePublish()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('billdesk.php'),
            ], 'billdesk-config');

            $this->publishes([
                __DIR__ . '/../stubs/controller.php' => app_path('Http/Controllers/Billdesk/Controller.php'),
            ], 'billdesk-controller');

            $this->publishes([
                __DIR__ . '/../public/assets' => public_path('assets/vendor/billdesk'),
            ], 'billdesk-controller');

            $this->publishes([
                __DIR__ . '/../resources/views/payment.blade.php' => resource_path('views/vendor/billdesk/payment.blade.php'),
            ], 'billdesk-views');

            $this->commands([
                BilldeskPublish::class,
                TransactionStatus::class,
            ]);
        }
    }
}
