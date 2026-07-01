<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Services\MenuGroupingService;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function __construct(private MenuGroupingService $menuGrouping) {}

    public function landing(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        return view('public.landing', compact('restaurant'));
    }

    public function menu(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        $menuItems = $restaurant->menuItems()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        ['foodGroups' => $foodGroups, 'drinkGroups' => $drinkGroups] = $this->menuGrouping->groupItems($menuItems);

        return view('public.menu', compact('restaurant', 'foodGroups', 'drinkGroups'));
    }
}
