<?php

declare(strict_types=1);

namespace MultiSessions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;

class Session
{
    private $session = 'default';
    private $id;
    private $config;

    public function __construct()
    {
        $this->id = $this->getId();
        $this->config = $this->getConfig();
    }

    public function session(string $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function set(string $key, $data): self
    {
        $session = $this->getSession();
        $id = $this->getId();

        Cache::driver($this->config['driver'])->remember($key, $this->config['lifetime'], function () use ($data) {
            return $data;
        });

        return $this;
    }

    public function get(string $key = null)
    {
        $name = $this->getSession();
        $id = $this->getId();

        return Cache::driver($this->config['driver'])->get("session_{$name}_{$id}");
    }

    public function has(string $key): bool
    {
        $session = $this->getSession();
        $id = $this->getId();

        return Cache::has("{$session}.{$id}.{$key}");
    }


    private function initIfEmpty(): void
    {
        $session = $this->getSession();
        $id = $this->getId();

        if (!Cache::has($session)) {
            Cache::put($session, []);
        }

        $data = Cache::get($session);

        if (!isset($data[$id])) {
            $data[$id] = [];
            Cache::put($session, $data);
        }
    }

    private function getId(): string
    {
        $session = $this->getSession();

        $hasCookie = Cookie::get('session_default');

        if ($hasCookie) {
            return Cookie::get($session);
        }

        $id = $this->generateId();

        Cookie::queue('session_default', $id, config("multisessions.{$session}.lifetime"));

        return $id;
    }

    private function getConfig(): array
    {
        return config("multisessions.{$this->session}");
    }

    private function generateId(): string
    {
        return Str::random(32);
    }
}