<?php

use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Cookie as SymfoneCookie;

Cookie::macro('getQueuedCookie', function ($name) {
    $cookies = collect(Cookie::getQueuedCookies())->mapWithKeys(function (SymfoneCookie $cookie) {
        return [$cookie->getName() => $cookie->getValue()];
    })->toArray();

    return $cookies[$name] ?? null;
});

Cookie::macro('hasQueuedCookie', function ($name) {
    $cookies = collect(Cookie::getQueuedCookies())->mapWithKeys(function (SymfoneCookie $cookie) {
        return [$cookie->getName() => $cookie->getValue()];
    })->toArray();
    
    return isset($cookies[$name]);
});