<?php

declare(strict_types=1);

namespace Arendach\MultiSessions\Providers;

use Illuminate\Support\ServiceProvider;
use Arendach\MultiSessions\Session;

class MultiSessionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        foreach (config('multisessions') as $key => $session) {
            $this->app->singleton(Session::abstractKey($key), function () use ($key) {
                return new Session($key);
            });
        }
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/multisessions.php' => config_path('multisessions.php'),
        ], 'multisessions');
    }
}