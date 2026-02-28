<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class PublicStorageController extends Controller
{
    public function show($path)
    {
        $path = ltrim((string) $path, '/');

        // Block path traversal.
        if ($path === '' || strpos($path, '..') !== false) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            abort(404);
        }

        $fullPath = $disk->path($path);
        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
