<?php

declare(strict_types=1);

namespace Arendach\MultiSessions\Middleware;

use Closure;
use Cache;
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
     * @return Response
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
        if (!method_exists($this->response, 'withCookie')) {
            return;
        }

        $sessions = config('multisessions');

        foreach ($sessions as $name => $session) {
            $this->rebootCookies($name, $session);
            $this->rebootCache($name, $session);
        }
    }

    public function rebootCookies(string $name, array $data): void
    {
        $id = $this->getId($name);

        $this->response->withCookie(cookie("session_$name", $id, $data['lifetime']));
    }

    public function rebootCache(string $name, array $data): void
    {
        $id = $this->getId($name);
        $cacheName = "session_{$name}_{$id}";

        $sessionData = Cache::driver($data['driver'])->has($cacheName) ? Cache::driver($data['driver'])->get($cacheName) : null;

        Cache::driver($data['driver'])->forget($cacheName);
        Cache::driver($data['driver'])->add($cacheName, $sessionData, $data['lifetime'] * 60);
    }

    private function getId(string $name): string
    {
        $name = "session_{$name}";

        if (isset($this->ids[$name])) {
            return $this->ids[$name];
        }

        if (Cookie::hasQueuedCookie($name)) {
            $id = Cookie::getQueuedCookie($name);
        } else {
            $id = $this->request->cookie($name) ? $this->request->cookie($name) : $this->generateId();
        }

        $this->ids[$name] = $id;

        return $id;
    }

    private function generateId(): string
    {
        return Str::random(32);
    }
}