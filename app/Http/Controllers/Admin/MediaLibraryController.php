<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Services\MediaLibraryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaLibraryController extends Controller
{
    public function __construct(
        private readonly MediaLibraryService $mediaLibraryService,
    ) {}

    public function index(Request $request): Response
    {
        $modelType = $request->query('model_type');
        $modelType = is_string($modelType) && $modelType !== '' ? $modelType : null;

        $collection = $request->query('collection');
        $collection = is_string($collection) && $collection !== '' ? $collection : null;

        $q = $request->query('q');
        $q = is_string($q) ? trim($q) : '';
        $q = $q !== '' ? $q : null;

        $allowedTypes = $this->mediaLibraryService->modelTypes()->all();
        if ($modelType !== null && ! in_array($modelType, $allowedTypes, true)) {
            $modelType = null;
        }

        $allowedCollections = $this->mediaLibraryService->collections($modelType)->all();
        if ($collection !== null && ! in_array($collection, $allowedCollections, true)) {
            $collection = null;
        }

        $filters = [
            'model_type' => $modelType,
            'collection_name' => $collection,
            'q' => $q,
        ];

        $media = $this->mediaLibraryService->paginate($filters, 24);

        return Inertia::render('Admin/MediaLibrary/Index', [
            'media' => MediaResource::collection($media),
            'filters' => [
                'model_type' => $modelType,
                'collection' => $collection,
                'q' => $q,
            ],
            'modelTypes' => collect($allowedTypes)->map(fn (string $type) => [
                'value' => $type,
                'label' => class_basename($type),
            ])->values()->all(),
            'collections' => collect($allowedCollections)->map(fn (string $name) => [
                'value' => $name,
                'label' => $name,
            ])->values()->all(),
        ]);
    }

    public function destroy(Media $medium): RedirectResponse
    {
        $this->mediaLibraryService->delete($medium);

        return redirect()
            ->back()
            ->with('success', 'Media deleted.');
    }
}
