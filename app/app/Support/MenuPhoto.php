<?php

namespace App\Support;

/**
 * Bundled stock photos for the seeded menu (config/menu_photos.php), looked
 * up by item name. Used instead of the generated SVG placeholders, never
 * instead of an image someone uploaded.
 */
class MenuPhoto
{
    public static function urlFor(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $key = config('menu_photos')[$name] ?? null;

        return $key === null ? null : "/images/menu/photos/{$key}.webp";
    }

    /** True for the SVG placeholders written by `menu:generate-images`. */
    public static function isGeneratedPlaceholder(?string $imagePath): bool
    {
        return $imagePath !== null
            && str_starts_with($imagePath, 'images/menu/')
            && str_ends_with($imagePath, '.svg');
    }
}
