<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequirePermission
{
    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ) {
        abort_unless(
            $request->user()?->permits($permission),
            403,
            'Anda tidak memiliki izin untuk fitur ini.'
        );

        return $next($request);
    }
}