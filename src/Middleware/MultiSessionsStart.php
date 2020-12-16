<?php

declare(strict_types=1);

namespace MultiSessions\Middleware;

use Closure;
use Cache;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class MultiSessionsStart
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    private $ids = [];

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->request = $request;

        $this->response = $next($request);

        $this->rebootSession();

        return $this->response;
    }

    public function rebootSession(): void
    {
        $sessions = config('multisessions');

        foreach ($sessions as $name => $session) {
            $this->rebootCookies($name, $session);
            $this->rebootCache($name, $session);
        }
    }

    public function rebootCookies(string $name, array $data): void
    {
        $id = $this->getId($name);
        
        $this->response->withCookie("session_$name", $id, $data['lifetime']);
    }

    public function rebootCache(string $name, array $data): void
    {
        $id = $this->getId($name);
        $cacheName = "session_{$name}_{$id}";

        Cache::remember($cacheName, $data['lifetime'], function () use ($data, $cacheName) {
            return Cache::driver($data['driver'])->has($cacheName)
                ? Cache::driver($data['driver'])->get($cacheName)
                : null;
        });
    }

    private function getId(string $name): string
    {
        $id = $this->request->cookie($name) ? $this->request->cookie($name) : $this->generateId();

        $this->ids[$name] = $id;

        return $id;
    }

    private function generateId(): string
    {
        return Str::random(32);
    }
}