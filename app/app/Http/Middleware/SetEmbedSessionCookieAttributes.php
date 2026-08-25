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
     *
     * On the entry route (public.booking.create), pass the "check_origin"
     * parameter to additionally skip the override when Sec-Fetch-Site is
     * same-origin — this is what lets the admin's own "Embed widget" preview
     * page (which loads that same route in a same-origin iframe) keep its
     * normal session cookie instead of getting it rewritten. Sec-Fetch-Site
     * only reflects the *immediate* request initiator, not the top-level
     * page's origin, so this check is only meaningful on the one route that
     * is actually loaded directly as `iframe.src` — every other route in the
     * booking journey is reached by same-document navigation from inside an
     * already-loaded iframe (a form POST, a redirect), which always reports
     * same-origin even when the iframe itself is still embedded cross-site.
     * Those routes must NOT run the origin check, or a real embedded visitor
     * would silently lose the partitioned cookie after the first page load.
     */
    public function handle(Request $request, Closure $next, string $mode = 'always'): Response
    {
        $shouldOverride = $request->boolean('embed');

        if ($shouldOverride && $mode === 'check_origin' && $request->header('Sec-Fetch-Site') === 'same-origin') {
            $shouldOverride = false;
        }

        if ($shouldOverride) {
            config([
                'session.same_site' => 'none',
                'session.secure' => true,
                'session.partitioned' => true,
            ]);
        }

        return $next($request);
    }
}
