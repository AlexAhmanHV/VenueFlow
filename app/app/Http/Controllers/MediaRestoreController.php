<?php

namespace App\Http\Controllers;

use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MediaRestoreController extends Controller
{
    public function __invoke(Request $request, string $token, ImageUploadService $imageUploadService): RedirectResponse
    {
        $restored = $imageUploadService->restoreFromTrashToken($token);

        return back()->with(
            'status',
            $restored ? 'Bild aterstalld.' : 'Aterstallning misslyckades eller token gick ut.'
        );
    }
}
