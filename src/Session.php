<?php

declare(strict_types=1);

namespace Arendach\MultiSessions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use Psr\SimpleCache\InvalidArgumentException;

class Session
{
    /**
     * Session name from config file
     *
     * @var string
     */
    private $session;

    /**
     * Session id
     *
     * @var string
     */
    private $id;

    /**
     * Array of session config
     *
     * @var array
     */
    private $config;

    /**
     * Session constructor.
     * @param string $session
     */
    public function __construct(string $session = 'default')
    {
        $this->session = $session;
        $this->config = $this->setConfig();
        $this->id = $this->setId();
    }

    /**
     * Change session runtime
     *
     * @param string $session
     * @return $this
     */
    public function session(string $session): self
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Set data into session
     *
     * @param string $key
     * @param $value
     * @return $this
     */
    public function set(string $key, $value): self
    {
        $session = $this->getKey();
        $data = $this->get();

        $data = array_merge($data, [$key => $value]);

        Cache::driver($this->config['driver'])->forget($session);
        Cache::driver($this->config['driver'])->add($session, $data, $this->config['lifetime'] * 60);

        return $this;
    }

    /**
     * Get data from session
     *
     * @param string|null $key
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get(string $key = null)
    {
        $session = $this->getKey();

        $data = Cache::driver($this->config['driver'])->get($session);

        return $key
            ? ($data[$key] ?? null)
            : (is_array($data) ? $data : []);
    }

    /**
     * Check if session has data
     *
     * @param string $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public function has(string $key): bool
    {
        $data = $this->get();

        return isset($data[$key]);
    }

    /**
     * Get session id from cookie or create new
     *
     * @return string
     */
    private function setId(): string
    {
        $session = "session_{$this->session}";

        if (Cookie::has($session)) {
            return Cookie::get($session);
        }

        $id = $this->generateId();

        Cookie::queue($session, $id, $this->config['lifetime']);

        return $id;
    }

    /**
     * Get config for this session
     *
     * @return array
     */
    private function setConfig(): array
    {
        return config("multisessions.{$this->session}");
    }

    /**
     * Generate unique string
     *
     * @return string
     */
    private function generateId(): string
    {
        return Str::random(32);
    }

    /**
     * Get session name(key) into cache storage
     *
     * @return string
     */
    private function getKey(): string
    {
        return "session_{$this->session}_{$this->id}";
    }
}