<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\SaveDishTemplateRequest;
use App\Models\DishTemplate;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;

class DishTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = DishTemplate::query();

        if ($request->filled('q')) {
            $q = trim((string) $request->query('q'));
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($request->query('status') === 'active') {
            $query->where('active', true);
        } elseif ($request->query('status') === 'inactive') {
            $query->where('active', false);
        }

        match ((string) $request->query('sort', 'name_asc')) {
            'name_desc' => $query->orderByDesc('name'),
            'price_asc' => $query->orderBy('base_price'),
            'price_desc' => $query->orderByDesc('base_price'),
            'latest' => $query->orderByDesc('id'),
            default => $query->orderBy('name'),
        };

        $templates = $query->paginate(20)->withQueryString();

        return view('platform.dish-templates.index', compact('templates'));
    }

    public function store(SaveDishTemplateRequest $request, ImageUploadService $imageUploadService)
    {
        DishTemplate::create([
            'name' => $request->string('name')->toString(),
            'description' => $request->string('description')->toString() ?: null,
            'base_price' => $request->input('base_price'),
            'active' => $request->boolean('active', true),
            'tags' => $request->string('tags')->toString()
                ? array_map('trim', explode(',', $request->string('tags')->toString()))
                : null,
            'image_path' => $request->hasFile('image')
                ? $imageUploadService->store(
                    $request->file('image'),
                    'uploads/catalog/dishes',
                    ['crop' => $this->cropFromRequest($request)]
                )
                : null,
        ]);

        return back()->with('status', 'Rattmall skapad.');
    }

    public function update(
        SaveDishTemplateRequest $request,
        DishTemplate $dishTemplate,
        ImageUploadService $imageUploadService
    ) {
        $payload = [
            'name' => $request->string('name')->toString(),
            'description' => $request->string('description')->toString() ?: null,
            'base_price' => $request->input('base_price'),
            'active' => $request->boolean('active', true),
            'tags' => $request->string('tags')->toString()
                ? array_map('trim', explode(',', $request->string('tags')->toString()))
                : null,
        ];

        $undoToken = null;
        if ($request->hasFile('image')) {
            if (! empty($dishTemplate->image_path)) {
                $trash = $imageUploadService->moveToTrash(
                    $dishTemplate->image_path,
                    DishTemplate::class,
                    (int) $dishTemplate->id,
                    'image_path',
                    $request->user()?->id
                );
                $undoToken = $trash?->token;
            }

            $payload['image_path'] = $imageUploadService->store(
                $request->file('image'),
                'uploads/catalog/dishes',
                ['crop' => $this->cropFromRequest($request)]
            );
        } elseif ($request->boolean('remove_image') && ! empty($dishTemplate->image_path)) {
            $trash = $imageUploadService->moveToTrash(
                $dishTemplate->image_path,
                DishTemplate::class,
                (int) $dishTemplate->id,
                'image_path',
                $request->user()?->id
            );
            $undoToken = $trash?->token;
            $payload['image_path'] = null;
        }

        $dishTemplate->update($payload);

        if ($undoToken) {
            session()->flash('undo_restore_url', route('media.restore', ['token' => $undoToken]));
            session()->flash('undo_message', 'Bild borttagen. Du kan angra i 20 minuter.');
        }

        return back()->with('status', 'Rattmall uppdaterad.');
    }

    public function destroy(Request $request, DishTemplate $dishTemplate, ImageUploadService $imageUploadService)
    {
        if (! empty($dishTemplate->image_path)) {
            $imageUploadService->remove($dishTemplate->image_path);
        }

        $dishTemplate->delete();

        return back()->with('status', 'Rattmall borttagen.');
    }

    private function cropFromRequest(Request $request): ?array
    {
        if (! $request->filled('crop_w') || ! $request->filled('crop_h')) {
            return null;
        }

        return [
            'x' => (int) $request->integer('crop_x'),
            'y' => (int) $request->integer('crop_y'),
            'w' => (int) $request->integer('crop_w'),
            'h' => (int) $request->integer('crop_h'),
        ];
    }
}
