<?php

namespace App\Console\Commands;

use App\Services\ImageUploadService;
use Illuminate\Console\Command;

class CleanupTrashedImages extends Command
{
    protected $signature = 'images:cleanup-trash';

    protected $description = 'Remove expired soft-deleted images from trash';

    public function handle(ImageUploadService $imageUploadService): int
    {
        $deleted = $imageUploadService->purgeExpiredTrash();
        $this->info("Deleted {$deleted} trashed images.");

        return self::SUCCESS;
    }
}
