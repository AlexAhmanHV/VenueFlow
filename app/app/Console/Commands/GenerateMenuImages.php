<?php

namespace App\Console\Commands;

use App\Models\DishTemplate;
use App\Models\DrinkTemplate;
use App\Models\MenuItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateMenuImages extends Command
{
    protected $signature = 'menu:generate-images';

    protected $description = 'Generate local SVG images for dish/drink templates and restaurant menu items.';

    public function handle(): int
    {
        $outputDir = public_path('images/menu');
        File::ensureDirectoryExists($outputDir);

        $this->generateForDishTemplates($outputDir);
        $this->generateForDrinkTemplates($outputDir);
        $this->generateForMenuItems($outputDir);

        $this->info('Menu images generated.');

        return self::SUCCESS;
    }

    private function generateForDishTemplates(string $outputDir): void
    {
        DishTemplate::query()->each(function (DishTemplate $template) use ($outputDir) {
            $filename = "dish-template-{$template->id}.svg";
            $relativePath = "images/menu/{$filename}";
            File::put(
                $outputDir.DIRECTORY_SEPARATOR.$filename,
                $this->svgForItem($template->name, 'food')
            );
            $template->forceFill(['image_path' => $relativePath])->save();
        });
    }

    private function generateForDrinkTemplates(string $outputDir): void
    {
        DrinkTemplate::query()->each(function (DrinkTemplate $template) use ($outputDir) {
            $filename = "drink-template-{$template->id}.svg";
            $relativePath = "images/menu/{$filename}";
            File::put(
                $outputDir.DIRECTORY_SEPARATOR.$filename,
                $this->svgForItem($template->name, 'drink')
            );
            $template->forceFill(['image_path' => $relativePath])->save();
        });
    }

    private function generateForMenuItems(string $outputDir): void
    {
        MenuItem::query()->each(function (MenuItem $item) use ($outputDir) {
            $kind = $this->menuItemKind($item);
            $filename = "menu-item-{$item->id}.svg";
            $relativePath = "images/menu/{$filename}";
            File::put(
                $outputDir.DIRECTORY_SEPARATOR.$filename,
                $this->svgForItem($item->name, $kind)
            );
            $item->forceFill(['image_path' => $relativePath])->save();
        });
    }

    private function menuItemKind(MenuItem $item): string
    {
        if ($item->drink_template_id !== null) {
            return 'drink';
        }

        if ($item->dish_template_id !== null) {
            return 'food';
        }

        $tags = collect($item->tags ?? [])->map(fn ($tag) => strtolower((string) $tag));
        $drinkTags = [
            'drink', 'beer', 'wine', 'cocktail', 'mocktail', 'cider',
            'coffee', 'tea', 'juice', 'soft', 'water', 'non_alcoholic',
        ];

        return $tags->intersect($drinkTags)->isNotEmpty() ? 'drink' : 'food';
    }

    private function svgForItem(string $name, string $kind): string
    {
        $title = e($name);
        $label = $kind === 'drink' ? 'DRYCK' : 'MAT';
        $gradientA = $kind === 'drink' ? '#0f766e' : '#9333ea';
        $gradientB = $kind === 'drink' ? '#0284c7' : '#f97316';

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800" viewBox="0 0 1200 800" role="img" aria-label="{$title}">
  <defs>
    <linearGradient id="g" x1="0" x2="1" y1="0" y2="1">
      <stop offset="0%" stop-color="{$gradientA}" />
      <stop offset="100%" stop-color="{$gradientB}" />
    </linearGradient>
  </defs>
  <rect width="1200" height="800" fill="url(#g)" />
  <circle cx="1000" cy="130" r="220" fill="rgba(255,255,255,0.12)" />
  <circle cx="170" cy="700" r="260" fill="rgba(255,255,255,0.1)" />
  <text x="84" y="145" font-size="46" font-family="Arial, sans-serif" fill="#ffffff" opacity="0.9">{$label}</text>
  <text x="84" y="470" font-size="86" font-family="Arial, sans-serif" font-weight="700" fill="#ffffff">{$title}</text>
  <text x="84" y="540" font-size="30" font-family="Arial, sans-serif" fill="rgba(255,255,255,0.85)">VenueFlow</text>
</svg>
SVG;
    }
}

