<?php

namespace MultiSessions;

use Illuminate\Support\ServiceProvider;

class MultiSessionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Session::class, function ($app) {
            return new Session();
        });
    }

    public function boot(): void
    {
        require __DIR__ . '/macro.php';

        $this->publishes([
            __DIR__ . '/config/multisessions.php' => config_path('multisessions.php'),
        ], 'multisessions');
    }
}