<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetEmbedSessionCookieAttributes
{
    /**
     * Gives the session cookie CHIPS-partitioned, cross-site-safe attributes
     * when the request is inside an embedded iframe (embed=1), so the guest
     * booking cart survives across steps even when the browser blocks
     * third-party cookies (Safari ITP, Firefox). Left untouched otherwise.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->boolean('embed') && $request->header('Sec-Fetch-Site') !== 'same-origin') {
            config([
                'session.same_site' => 'none',
                'session.secure' => true,
                'session.partitioned' => true,
            ]);
        }

        return $next($request);
    }
}
