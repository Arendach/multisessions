<?php

declare(strict_types=1);

namespace Arendach\MultiSessions\Middleware;

use Arendach\MultiSessions\Session;
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

        if (method_exists($this->response, 'withCookie')) {
            $this->rebootSession();
        }

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
        $id = Session::instance($name)->getId();
        $key = Session::abstractKey($name);

        $this->response->withCookie(cookie($key, $id, $data['lifetime']));
    }

    public function rebootCache(string $name, array $data): void
    {
        $cacheName = Session::instance($name)->getKey();

        $sessionData = Cache::driver($data['driver'])->has($cacheName) ? Cache::driver($data['driver'])->get($cacheName) : null;

        Cache::driver($data['driver'])->forget($cacheName);
        Cache::driver($data['driver'])->add($cacheName, $sessionData, $data['lifetime'] * 60);
    }
}