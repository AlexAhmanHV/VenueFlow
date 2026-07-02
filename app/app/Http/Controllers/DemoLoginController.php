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
                'email_verified_at' => now(),
            ]
        );

        if (! Hash::check('password', $user->password)) {
            $user->update([
                'password' => Hash::make('password'),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
            $user->refresh();
        }

        Auth::login($user);

        $membership = $user->memberships()->with('restaurant')->first();
        if ($membership?->restaurant?->slug) {
            return redirect()->route('restaurant.admin.dashboard', $membership->restaurant->slug);
        }

        return redirect()->route('dashboard');
    }
}
