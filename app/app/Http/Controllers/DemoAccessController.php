<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DemoAccessController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (! config('demo.public_mode')) {
            return redirect()->route('home');
        }

        $flag = (string) config('demo.session_flag', 'demo.full_access_granted');
        $unlocked = $request->session()->get($flag) === true;

        return view('demo.full-access', compact('unlocked'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (! config('demo.public_mode')) {
            return redirect()->route('home');
        }

        $request->validate([
            'access_key' => ['required', 'string', 'max:200'],
        ]);

        $expected = (string) config('demo.full_access_key', '');
        if ($expected === '') {
            return back()->withErrors([
                'access_key' => 'Full demo access is not configured on this environment.',
            ]);
        }

        $provided = (string) $request->string('access_key')->toString();
        if (! hash_equals($expected, $provided)) {
            return back()->withErrors([
                'access_key' => 'Invalid access key.',
            ])->withInput();
        }

        $flag = (string) config('demo.session_flag', 'demo.full_access_granted');
        $request->session()->put($flag, true);
        $request->session()->regenerate();

        return redirect()->route('login')->with('status', 'Full demo access unlocked for this session.');
    }

    public function revoke(Request $request): RedirectResponse
    {
        $flag = (string) config('demo.session_flag', 'demo.full_access_granted');
        $request->session()->forget($flag);

        return redirect()->route('home')->with('status', 'Full demo access removed from this session.');
    }
}
