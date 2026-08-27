<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class HandleLocale
{
    /**
     * Pick the request locale from the `locale` cookie the frontend writes.
     *
     * Arabic is the app default, so a missing cookie means Arabic — the same
     * assumption the Svelte side makes. Getting this wrong is what made flash
     * toasts come back in English on an Arabic UI.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($request->cookie('locale') === 'en' ? 'en' : 'ar');

        return $next($request);
    }
}
