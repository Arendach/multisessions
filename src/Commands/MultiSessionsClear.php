<?php

namespace MultiSessions\Commaands;

class MultiSessionsClear extends Command
{
    protected $signature = 'multisessions:clear';

    protected $description = 'Clear multisessions';

    public function handle()
    {
        collect(config('multisessions'))->unique('driver')->each(function (string $driver){
            \Cache::driver($driver)->forget();
        });
    }
}
