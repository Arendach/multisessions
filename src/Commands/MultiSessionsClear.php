<?php

namespace MultiSessions\Commaands;

use Illuminate\Support\Facades\Cache;

class MultiSessionsClear extends Command
{
    protected $signature = 'multisessions:clear';

    protected $description = 'Clear multisessions';

    public function handle(): void
    {
        $sessions = config('multisessions');

        foreach ($sessions as $name => $session) {
            Cache::driver($session['driver'])->forget();
        }
    }
}
