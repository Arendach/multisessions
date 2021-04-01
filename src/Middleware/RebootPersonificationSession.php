<?php

declare(strict_types=1);

namespace Arendach\MultiSessions\Middleware;

use Throwable;
use Crypt;
use Closure;
use Log;
use Arendach\MultiSessions\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class RebootPersonificationSession
 * @package Arendach\MultiSessions\Middleware
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
     * RebootPersonificationSession constructor.
     */
    public function __construct()
    {
        $this->cacheStorage = Session::instance('personification');
    }

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

        $oldIp = $this->cacheStorage->get('ip_address');
        $newIp = $ip;

        if ($oldIp != null && $oldIp != $newIp) {
            if (class_exists('\Arendach\VodafoneName\Name')) {
                resolve('\Arendach\VodafoneName\Name')->rebootSession();
            }
            
            if (class_exists('\Arendach\VodafoneMsisdn\Msisdn')) {
                resolve('\Arendach\VodafoneMsisdn\Msisdn')->rebootSession();
            }
        }

        $this->cacheStorage->set('ip_address', $newIp);

        return $next($request);
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getIp($request): string
    {
        $userAddress = $this->getXUserAddress($request);

        return $userAddress ? $userAddress : $request->ip();
    }

    /**
     * @param Request $request
     * @return string|null
     */
    private function getXUserAddress(Request $request): ?string
    {
        $ipHeader = $request->get('x-user-address');

        if (!$ipHeader) {
            return null;
        }

        if (filter_var($ipHeader, FILTER_VALIDATE_IP)) {
            return $ipHeader;
        }

        try {

            return Crypt::decryptString($ipHeader);

        } catch (Throwable $exception) {

            Log::error($exception->getMessage() . PHP_EOL . $exception->getFile() . PHP_EOL . $exception->getTraceAsString());

            return '127.0.0.1';

        }
    }
}