<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DemoLoginController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $user = User::firstOrCreate(
            ['email' => 'owner@demo.test'],
            [
                'name' => 'Demo Owner',
                'password' => Hash::make('password'),
            ]
        );

        $needsSave = false;

        if (! Hash::check('password', $user->password)) {
            $user->password = Hash::make('password');
            $needsSave = true;
        }

        if (is_null($user->email_verified_at)) {
            $user->email_verified_at = now();
            $needsSave = true;
        }

        if ($needsSave) {
            $user->saveQuietly();
        }

        Auth::login($user);

        $membership = $user->memberships()->with('restaurant')->first();
        if ($membership?->restaurant?->slug) {
            return redirect()->route('restaurant.admin.dashboard', $membership->restaurant->slug);
        }

        return redirect()->route('dashboard');
    }
}
