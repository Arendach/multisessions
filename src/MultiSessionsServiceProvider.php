<?php

namespace Arendach\MultiSessions;

use Illuminate\Support\ServiceProvider;

class MultiSessionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        require __DIR__ . '/macro.php';

        $this->publishes([
            __DIR__ . '/config/multisessions.php' => config_path('multisessions.php'),
        ], 'multisessions');

        foreach (config('multisessions') as $key => $session) {
            $this->app->singleton(Session::abstractKey($key), function () use ($key) {
                return new Session($key);
            });
        }
    }
}