<?php

declare(strict_types=1);

namespace MultiSessions;

use Illuminate\Support\Facades\Cache;

class Storage
{
    private $driver;
    private $id;

    public function id(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function set()
    {
        Cache::driver('file')
    }

    public function get(string $key, string $id)
    {

    }
}