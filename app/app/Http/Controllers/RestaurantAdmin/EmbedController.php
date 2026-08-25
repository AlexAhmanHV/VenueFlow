<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmbedController extends Controller
{
    public function show(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');
        $this->authorize('view', $restaurant);

        $embedSnippet = sprintf(
            '<script src="%s" data-slug="%s"></script>',
            url('/embed.js'),
            $restaurant->slug
        );

        return view('restaurant-admin.embed.show', compact('restaurant', 'embedSnippet'));
    }
}
