<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RestaurantController extends Controller
{
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

        $foodItems = $menuItems->filter(fn (MenuItem $item) => ! $this->isDrink($item))->values();
        $drinkItems = $menuItems->filter(fn (MenuItem $item) => $this->isDrink($item))->values();

        $foodGroups = $this->groupFoodItems($foodItems);
        $drinkGroups = $this->groupDrinkItems($drinkItems);

        return view('public.menu', compact('restaurant', 'foodGroups', 'drinkGroups'));
    }

    private function isDrink(MenuItem $item): bool
    {
        if ($item->drink_template_id !== null) {
            return true;
        }

        if ($item->dish_template_id !== null) {
            return false;
        }

        $tags = $this->normalizedTags($item);
        $drinkTags = [
            'drink', 'beer', 'wine', 'cocktail', 'mocktail', 'cider',
            'coffee', 'tea', 'juice', 'soft', 'water', 'non_alcoholic',
        ];

        return $tags->intersect($drinkTags)->isNotEmpty();
    }

    private function groupFoodItems(Collection $items): Collection
    {
        return collect([
            'starters' => $items->filter(fn (MenuItem $item) => $this->hasAnyTag($item, ['snacks', 'starter', 'sharing']))->sortBy('sort_order')->values(),
            'mains' => $items->filter(fn (MenuItem $item) => $this->hasAnyTag($item, ['main']))->sortBy('sort_order')->values(),
            'desserts' => $items->filter(fn (MenuItem $item) => $this->hasAnyTag($item, ['dessert']))->sortBy('sort_order')->values(),
            'other_food' => $items->filter(fn (MenuItem $item) => ! $this->hasAnyTag($item, ['snacks', 'starter', 'sharing', 'main', 'dessert']))->sortBy('sort_order')->values(),
        ])->filter(fn (Collection $group) => $group->isNotEmpty());
    }

    private function groupDrinkItems(Collection $items): Collection
    {
        return collect([
            'non_alcoholic' => $items->filter(fn (MenuItem $item) => $this->hasAnyTag($item, ['non_alcoholic', 'soft', 'water', 'juice', 'mocktail', 'energy']))->sortBy('sort_order')->values(),
            'beer_cider' => $items->filter(fn (MenuItem $item) => $this->hasAnyTag($item, ['beer', 'cider']))->sortBy('sort_order')->values(),
            'wine' => $items->filter(fn (MenuItem $item) => $this->hasAnyTag($item, ['wine']))->sortBy('sort_order')->values(),
            'cocktails' => $items->filter(fn (MenuItem $item) => $this->hasAnyTag($item, ['cocktail']))->sortBy('sort_order')->values(),
            'coffee_tea' => $items->filter(fn (MenuItem $item) => $this->hasAnyTag($item, ['coffee', 'tea']))->sortBy('sort_order')->values(),
            'other_drinks' => $items->filter(fn (MenuItem $item) => ! $this->hasAnyTag($item, ['non_alcoholic', 'soft', 'water', 'juice', 'mocktail', 'energy', 'beer', 'cider', 'wine', 'cocktail', 'coffee', 'tea']))->sortBy('sort_order')->values(),
        ])->filter(fn (Collection $group) => $group->isNotEmpty());
    }

    private function hasAnyTag(MenuItem $item, array $tags): bool
    {
        return $this->normalizedTags($item)->intersect($tags)->isNotEmpty();
    }

    private function normalizedTags(MenuItem $item): Collection
    {
        return collect($item->tags ?? [])->map(fn ($tag) => strtolower((string) $tag));
    }
}
