<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;

class LogVisit
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $isLoggedIn = $request->user() !== null;

        $isPageRequest = $request->isMethod('GET')
            && ! $request->ajax();

        $isSuccessful = $response->getStatusCode() === 200;

        $isHtmlResponse = str_contains(
            $response->headers->get('Content-Type', ''),
            'text/html'
        );

        if (
            $isLoggedIn
            && $isPageRequest
            && $isSuccessful
            && $isHtmlResponse
        ) {
            $sessionKey = 'cms_visit_'.$request->user()->id;

            $hasPreviousVisit = $request->session()->has($sessionKey);

            $isNewVisit = ! $hasPreviousVisit
                || $request->session()->get($sessionKey)
                    < now()->subMinutes(30)->timestamp;

            if ($isNewVisit) {
                ActivityLog::record(
                    'VISIT',
                    'Kunjungan aplikasi'
                );
            }

            $request->session()->put(
                $sessionKey,
                now()->timestamp
            );
        }

        return $response;
    }
}