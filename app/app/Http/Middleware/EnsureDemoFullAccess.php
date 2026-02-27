<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDemoFullAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('demo.public_mode')) {
            return $next($request);
        }

        $flag = (string) config('demo.session_flag', 'demo.full_access_granted');
        if ($request->session()->get($flag) === true) {
            return $next($request);
        }

        return $this->redirectToAccessPage();
    }

    private function redirectToAccessPage(): RedirectResponse
    {
        return redirect()
            ->route('demo.access.show')
            ->withErrors([
                'demo_access' => 'Privileged demo routes are locked. Unlock full demo access first.',
            ]);
    }
}
