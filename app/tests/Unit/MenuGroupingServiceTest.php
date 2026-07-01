<?php

namespace Tests\Unit;

use App\Models\MenuItem;
use App\Services\MenuGroupingService;
use PHPUnit\Framework\TestCase;

class MenuGroupingServiceTest extends TestCase
{
    private MenuGroupingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MenuGroupingService();
    }

    private function item(array $attrs): MenuItem
    {
        $item = new MenuItem();
        $item->forceFill(array_merge([
            'name'             => 'Test item',
            'price'            => 100,
            'active'           => true,
            'sort_order'       => 10,
            'dish_template_id' => null,
            'drink_template_id' => null,
            'tags'             => [],
        ], $attrs));

        return $item;
    }

    // ── isDrink ───────────────────────────────────────────────────────────────

    public function test_item_with_drink_template_is_drink(): void
    {
        $item = $this->item(['drink_template_id' => 42]);
        $this->assertTrue($this->service->isDrink($item));
    }

    public function test_item_with_dish_template_is_not_drink(): void
    {
        $item = $this->item(['dish_template_id' => 7]);
        $this->assertFalse($this->service->isDrink($item));
    }

    public function test_item_with_beer_tag_is_drink(): void
    {
        $item = $this->item(['tags' => ['beer']]);
        $this->assertTrue($this->service->isDrink($item));
    }

    public function test_item_with_no_drink_tags_is_not_drink(): void
    {
        $item = $this->item(['tags' => ['main', 'popular']]);
        $this->assertFalse($this->service->isDrink($item));
    }

    public function test_tag_matching_is_case_insensitive(): void
    {
        $item = $this->item(['tags' => ['WINE']]);
        $this->assertTrue($this->service->isDrink($item));
    }

    public function test_null_tags_treated_as_empty(): void
    {
        $item = $this->item(['tags' => null]);
        $this->assertFalse($this->service->isDrink($item));
    }

    // ── groupItems ────────────────────────────────────────────────────────────

    public function test_food_and_drink_items_are_split(): void
    {
        $food  = $this->item(['tags' => ['main']]);
        $drink = $this->item(['tags' => ['beer']]);

        ['foodGroups' => $foodGroups, 'drinkGroups' => $drinkGroups] =
            $this->service->groupItems(collect([$food, $drink]));

        $allFood  = $foodGroups->flatten();
        $allDrink = $drinkGroups->flatten();

        $this->assertCount(1, $allFood);
        $this->assertCount(1, $allDrink);
    }

    public function test_food_item_lands_in_correct_group(): void
    {
        $starter = $this->item(['tags' => ['starter']]);
        $main    = $this->item(['tags' => ['main']]);
        $dessert = $this->item(['tags' => ['dessert']]);
        $other   = $this->item(['tags' => ['random']]);

        ['foodGroups' => $groups] =
            $this->service->groupItems(collect([$starter, $main, $dessert, $other]));

        $this->assertCount(1, $groups->get('starters'));
        $this->assertCount(1, $groups->get('mains'));
        $this->assertCount(1, $groups->get('desserts'));
        $this->assertCount(1, $groups->get('other_food'));
    }

    public function test_drink_item_lands_in_correct_group(): void
    {
        $beer    = $this->item(['tags' => ['beer']]);
        $wine    = $this->item(['tags' => ['wine']]);
        $coffee  = $this->item(['tags' => ['coffee']]);
        $soft    = $this->item(['tags' => ['soft', 'non_alcoholic']]);
        $unknown = $this->item(['drink_template_id' => 1, 'tags' => []]);

        ['drinkGroups' => $groups] =
            $this->service->groupItems(collect([$beer, $wine, $coffee, $soft, $unknown]));

        $this->assertCount(1, $groups->get('beer_cider'));
        $this->assertCount(1, $groups->get('wine'));
        $this->assertCount(1, $groups->get('coffee_tea'));
        $this->assertCount(1, $groups->get('non_alcoholic'));
        $this->assertCount(1, $groups->get('other_drinks'));
    }

    public function test_empty_groups_are_not_included(): void
    {
        $main = $this->item(['tags' => ['main']]);

        ['foodGroups' => $groups] =
            $this->service->groupItems(collect([$main]));

        $this->assertNull($groups->get('starters'));
        $this->assertNull($groups->get('desserts'));
        $this->assertNotNull($groups->get('mains'));
    }
}
