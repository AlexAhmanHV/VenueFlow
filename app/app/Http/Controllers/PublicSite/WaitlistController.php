<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\WaitlistEntry;
use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    public function create(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        return view('public.waitlist.create', compact('restaurant'));
    }

    public function store(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        $validated = $request->validate([
            'customer_name'  => 'required|string|max:120',
            'email'          => 'required|email|max:180',
            'phone'          => 'nullable|string|max:30',
            'party_size'     => 'required|integer|min:1|max:50',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'nullable|date_format:H:i',
            'note'           => 'nullable|string|max:500',
        ]);

        WaitlistEntry::create(array_merge($validated, [
            'restaurant_id' => $restaurant->id,
        ]));

        return redirect()
            ->route('public.landing', $restaurant->slug)
            ->with('status', 'Du är nu i kö! Vi meddelar dig om en tid öppnar sig.');
    }
}
