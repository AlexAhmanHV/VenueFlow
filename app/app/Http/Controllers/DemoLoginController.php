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
        $user = User::where('email', 'owner@demo.test')->firstOrFail();

        Auth::login($user);

        return redirect('/r/golfbaren/admin/dashboard');
    }
}
