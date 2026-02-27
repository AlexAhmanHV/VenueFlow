<?php

namespace App\Services;

use App\Models\ImageTrashItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    public function store(UploadedFile $file, string $folder, array $options = []): string
    {
        $disk = $options['disk'] ?? 'public';
        $maxWidth = (int) ($options['max_width'] ?? 1920);
        $maxHeight = (int) ($options['max_height'] ?? 1080);
        $quality = (int) ($options['quality'] ?? 82);
        $crop = is_array($options['crop'] ?? null) ? $options['crop'] : null;

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $baseName = now()->format('YmdHis').'-'.Str::random(10);
        $sourcePath = trim($folder, '/').'/'.$baseName.'.'.$extension;

        $binary = $file->get();
        $processed = $this->processImageBinary($binary, $extension, $maxWidth, $maxHeight, $quality, $crop);

        Storage::disk($disk)->put($sourcePath, $processed['encoded']);

        if ($processed['webp'] !== null) {
            $webpPath = trim($folder, '/').'/'.$baseName.'.webp';
            Storage::disk($disk)->put($webpPath, $processed['webp']);
            Storage::disk($disk)->delete($sourcePath);

            return 'storage/'.$webpPath;
        }

        return 'storage/'.$sourcePath;
    }

    public function remove(string $path, string $disk = 'public'): void
    {
        if ($path === '') {
            return;
        }

        $normalized = $this->normalizePath($path);
        Storage::disk($disk)->delete($normalized);
    }

    public function moveToTrash(
        string $path,
        ?string $modelType = null,
        ?int $modelId = null,
        string $columnName = 'image_path',
        ?int $actorUserId = null,
        int $ttlMinutes = 20,
        string $disk = 'public'
    ): ?ImageTrashItem {
        if ($path === '') {
            return null;
        }

        $normalized = $this->normalizePath($path);
        if (! Storage::disk($disk)->exists($normalized)) {
            return null;
        }

        $trashPath = 'uploads/trash/'.now()->format('Y/m/d').'/'.Str::random(16).'-'.basename($normalized);
        Storage::disk($disk)->move($normalized, $trashPath);

        return ImageTrashItem::query()->create([
            'token' => Str::random(64),
            'disk' => $disk,
            'original_path' => $normalized,
            'trash_path' => $trashPath,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'column_name' => $columnName,
            'deleted_by_user_id' => $actorUserId,
            'expires_at' => Carbon::now()->addMinutes($ttlMinutes),
        ]);
    }

    public function restoreFromTrashToken(string $token): bool
    {
        $item = ImageTrashItem::query()
            ->where('token', $token)
            ->whereNull('restored_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $item) {
            return false;
        }

        $disk = Storage::disk($item->disk);
        if (! $disk->exists($item->trash_path)) {
            return false;
        }

        $destinationDir = dirname($item->original_path);
        if (! $disk->exists($destinationDir)) {
            $disk->makeDirectory($destinationDir);
        }

        $disk->move($item->trash_path, $item->original_path);

        if ($item->model_type && $item->model_id) {
            $modelClass = $item->model_type;
            if (class_exists($modelClass)) {
                $model = $modelClass::query()->find($item->model_id);
                if ($model && isset($model->{$item->column_name}) && empty($model->{$item->column_name})) {
                    $model->update([$item->column_name => $item->original_path]);
                }
            }
        }

        $item->forceFill(['restored_at' => now()])->save();

        return true;
    }

    public function purgeExpiredTrash(): int
    {
        $items = ImageTrashItem::query()
            ->whereNull('restored_at')
            ->where('expires_at', '<=', now())
            ->get();

        $deleted = 0;
        foreach ($items as $item) {
            Storage::disk($item->disk)->delete($item->trash_path);
            $item->delete();
            $deleted++;
        }

        return $deleted;
    }

    private function normalizePath(string $path): string
    {
        $normalized = ltrim($path, '/');
        if (str_starts_with($normalized, 'storage/')) {
            return substr($normalized, strlen('storage/'));
        }

        return $normalized;
    }

    private function processImageBinary(
        string $binary,
        string $extension,
        int $maxWidth,
        int $maxHeight,
        int $quality,
        ?array $crop = null
    ): array {
        if (! function_exists('imagecreatefromstring')) {
            return ['encoded' => $binary, 'webp' => null];
        }

        $image = @imagecreatefromstring($binary);
        if (! $image) {
            return ['encoded' => $binary, 'webp' => null];
        }

        $srcW = imagesx($image);
        $srcH = imagesy($image);
        [$cropX, $cropY, $cropW, $cropH] = $this->sanitizeCrop($crop, $srcW, $srcH);

        $cropped = imagecreatetruecolor($cropW, $cropH);
        imagealphablending($cropped, false);
        imagesavealpha($cropped, true);
        imagecopy($cropped, $image, 0, 0, $cropX, $cropY, $cropW, $cropH);
        imagedestroy($image);

        [$dstW, $dstH] = $this->fitSize($cropW, $cropH, $maxWidth, $maxHeight);
        $final = imagecreatetruecolor($dstW, $dstH);
        imagealphablending($final, false);
        imagesavealpha($final, true);
        imagecopyresampled($final, $cropped, 0, 0, 0, 0, $dstW, $dstH, $cropW, $cropH);
        imagedestroy($cropped);

        $encoded = $this->encodeByExtension($final, $extension, $quality);
        $webp = $this->encodeWebp($final, $quality);
        imagedestroy($final);

        return ['encoded' => $encoded, 'webp' => $webp];
    }

    private function sanitizeCrop(?array $crop, int $width, int $height): array
    {
        if (! $crop) {
            return [0, 0, $width, $height];
        }

        $x = max(0, (int) ($crop['x'] ?? 0));
        $y = max(0, (int) ($crop['y'] ?? 0));
        $w = max(1, (int) ($crop['w'] ?? $width));
        $h = max(1, (int) ($crop['h'] ?? $height));

        if ($x >= $width || $y >= $height) {
            return [0, 0, $width, $height];
        }

        $w = min($w, $width - $x);
        $h = min($h, $height - $y);

        return [$x, $y, $w, $h];
    }

    private function fitSize(int $width, int $height, int $maxWidth, int $maxHeight): array
    {
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return [$width, $height];
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height);

        return [(int) floor($width * $ratio), (int) floor($height * $ratio)];
    }

    private function encodeByExtension(\GdImage $image, string $extension, int $quality): string
    {
        ob_start();
        if (in_array($extension, ['jpg', 'jpeg'], true) && function_exists('imagejpeg')) {
            imagejpeg($image, null, max(1, min($quality, 100)));
        } elseif ($extension === 'png' && function_exists('imagepng')) {
            imagepng($image, null, 6);
        } elseif ($extension === 'gif' && function_exists('imagegif')) {
            imagegif($image);
        } else {
            imagepng($image, null, 6);
        }

        return (string) ob_get_clean();
    }

    private function encodeWebp(\GdImage $image, int $quality): ?string
    {
        if (! function_exists('imagewebp')) {
            return null;
        }

        ob_start();
        imagewebp($image, null, max(1, min($quality, 100)));
        return (string) ob_get_clean();
    }
}
