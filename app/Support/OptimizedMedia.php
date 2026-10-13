<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class OptimizedMedia
{
    public const IMAGE_WIDTHS = ['image' => 1280, 'card' => 640, 'poster-mobile' => 720];

    public function source(?string $path): ?string
    {
        if (! $path || preg_match('~^(?:[a-z]+:)?//~i', $path)) {
            return null;
        }

        $path = preg_replace('~^/?storage/~', '', $path);
        if (str_starts_with($path, '/') || in_array('..', explode('/', $path), true)) {
            return null;
        }

        $disk = Storage::disk('public');
        $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);

        if ($webp !== $path && $disk->exists($webp)) {
            $path = $webp;
        }

        return $disk->exists($path) ? $path : null;
    }

    public function derivative(string $source, string $profile): string
    {
        $disk = Storage::disk('public');
        $version = hash('sha256', implode('|', [$source, $disk->size($source), $disk->lastModified($source), $profile, 'v1']));
        $extension = str_starts_with($profile, 'video-') ? 'mp4' : 'webp';

        return 'umami/optimized/'.$version.'.'.$extension;
    }

    public function url(?string $path, string $profile = 'image'): ?string
    {
        $source = $this->source($path);
        if ($source === null) {
            return null;
        }

        $optimized = $this->derivative($source, $profile);

        return Storage::disk('public')->exists($optimized)
            ? Storage::disk('public')->url($optimized)
            : null;
    }
}
