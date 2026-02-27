<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDemoReadOnlyAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('demo.public_mode')) {
            return $next($request);
        }

        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        $flag = (string) config('demo.session_flag', 'demo.full_access_granted');
        if ($request->session()->get($flag) === true) {
            return $next($request);
        }

        abort(403, 'Read-only demo mode. Unlock full demo access to make changes.');
    }
}
