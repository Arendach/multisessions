<?php

declare(strict_types=1);

namespace Arendach\MultiSessions\Middleware;

use Closure;
use Arendach\MultiSessions\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Arendach\VodafoneName\Name;
use Arendach\VodafoneMsisdn\Msisdn;

/**
 * Class DestroyMultiSessionAfterChangeIp
 * @package Arendach\MultiSessions\Middlewares
 *
 * Need require arendach/vodafone-name, arendach/vodafone-msidn
 */
class RebootPersonificationSession
{
    /**
     * @var Session
     */
    private $cacheStorage;

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        $ip = $this->getIp($request);

        if (!$ip) {
            return $next($request);
        }

        $oldIp = $this->getCacheStorage()->get('ip_address');
        $newIp = $ip;

        if ($oldIp != $newIp) {
            resolve(Msisdn::class)->rebootSession();
            resolve(Name::class)->rebootSession();
        }

        $this->getCacheStorage()->set('ip_address', $newIp);

        return $next($request);
    }

    private function getCacheStorage(): Session
    {
        $abstract = Session::abstractKey('personification');

        if (!$this->cacheStorage) {
            $this->cacheStorage = app($abstract);
        }

        return $this->cacheStorage;
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getIp($request): string
    {
        $ipHeader = $request->header('X-USER-IP-ADDRESS');

        return $ipHeader ? $ipHeader : $request->ip();
    }
}