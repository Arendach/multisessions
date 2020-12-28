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

        include __DIR__ . '/macro.php';
    }

    public function boot(): void
    {
        include __DIR__ . '/macro.php';

        $this->publishes([
            __DIR__ . '/config/multisessions.php' => config_path('multisessions.php'),
        ], 'multisessions');
    }
}