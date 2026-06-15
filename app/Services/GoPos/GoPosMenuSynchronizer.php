<?php

namespace App\Services\GoPos;

use App\Models\MenuItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GoPosMenuSynchronizer
{
    public function sync(array $export, bool $dryRun = false, bool $refreshImages = false): array
    {
        $data = $export['data'] ?? [];
        $items = collect($data['items'] ?? []);

        $stats = [
            'categories_created' => 0,
            'categories_updated' => 0,
            'categories_disabled' => 0,
            'items_created' => 0,
            'items_updated' => 0,
            'items_disabled' => 0,
            'items_skipped' => 0,
            'images_downloaded' => 0,
            'images_failed' => 0,
        ];

        foreach ($items as $payload) {
            if (($payload['status'] ?? null) !== 'ENABLED') {
                continue;
            }

            $goposItemId = (int) ($payload['id'] ?? 0);
            if (! $goposItemId) {
                continue;
            }

            $item = $this->findItem($goposItemId, $payload['name'] ?? 'item');
            if (! $item) {
                $stats['items_skipped']++;

                continue;
            }

            $imageUrl = Arr::get($payload, 'image_link.default_link');
            $imagePath = $item->image;

            if ($imageUrl && (! $imagePath || $item->source_image !== $imageUrl || $refreshImages)) {
                $downloadedImagePath = $dryRun ? null : $this->downloadImage($imageUrl, $goposItemId);

                if ($downloadedImagePath) {
                    $imagePath = $downloadedImagePath;
                    $stats['images_downloaded']++;
                } elseif (! $dryRun) {
                    $stats['images_failed']++;
                }
            }

            $currentName = $item->exists ? $item->getTranslations('name') : [];

            $attributes = [
                'gopos_id' => $goposItemId,
                'gopos_category_id' => $payload['category_id'] ?? null,
                'gopos_tax_id' => $payload['tax_id'] ?? null,
                'gopos_joint_id' => $payload['joint_id'] ?? null,
                'name' => $this->translatedValue($payload['name'] ?? 'Item', $payload['translations'] ?? [], 'name', $currentName),
                'slug' => $item->slug,
                'description' => $item->getTranslations('description'),
                'price' => $this->priceLabel($payload['price'] ?? null),
                'image' => $imagePath,
                'source_image' => $imageUrl,
                'is_active' => true,
                'sort_order' => $item->sort_order,
                'gopos_payload' => $payload,
                'gopos_synced_at' => now(),
            ];

            if (! $dryRun) {
                $item->fill($attributes)->save();
            }

            $stats['items_updated']++;
        }

        return $stats;
    }

    private function translatedValue(string $fallback, array $translations, string $field, array $current = []): array
    {
        $value = [
            'pl' => $fallback,
            'uk' => $current['uk'] ?? $fallback,
            'en' => $current['en'] ?? $fallback,
        ];

        foreach ($translations as $translation) {
            $locale = $translation['locale'] ?? null;
            if (in_array($locale, ['pl', 'uk', 'en'], true) && filled($translation[$field] ?? null)) {
                $value[$locale] = $translation[$field];
            }
        }

        return $value;
    }

    private function priceLabel(?array $price): ?string
    {
        $amount = $price['amount'] ?? null;
        $currency = $price['currency'] ?? 'PLN';

        if ($amount === null) {
            return null;
        }

        $formatted = ((float) $amount == (int) $amount)
            ? (string) (int) $amount
            : rtrim(rtrim(number_format((float) $amount, 2, ',', ''), '0'), ',');

        return $currency === 'PLN' ? "{$formatted} zł" : "{$formatted} {$currency}";
    }

    private function downloadImage(string $url, int $goposItemId): ?string
    {
        try {
            $baseName = $goposItemId.'-'.substr(sha1($url), 0, 10);
            $directory = 'umami/gopos/items';
            $path = "{$directory}/{$baseName}.webp";
            $absolutePath = Storage::disk('public')->path($path);
            $temporaryPath = Storage::disk('public')->path("{$directory}/{$baseName}.tmp");

            if (! is_dir(dirname($absolutePath))) {
                mkdir(dirname($absolutePath), 0755, true);
            }

            $response = Http::timeout(60)
                ->withOptions(['sink' => $temporaryPath])
                ->get($url);

            if (! $response->successful()) {
                if (is_file($temporaryPath)) {
                    unlink($temporaryPath);
                }

                return null;
            }

            if ($this->convertToWebp($temporaryPath, $absolutePath)) {
                unlink($temporaryPath);
                chmod($absolutePath, 0644);
                Storage::disk('public')->setVisibility($path, 'public');

                return $path;
            }

            $fallbackExtension = $this->imageExtension($response->header('Content-Type'), $url);
            $fallbackPath = "{$directory}/{$baseName}.{$fallbackExtension}";
            $fallbackAbsolutePath = Storage::disk('public')->path($fallbackPath);
            rename($temporaryPath, $fallbackAbsolutePath);
            chmod($fallbackAbsolutePath, 0644);
            Storage::disk('public')->setVisibility($fallbackPath, 'public');

            return $fallbackPath;
        } catch (\Throwable) {
            return null;
        }
    }

    private function convertToWebp(string $sourcePath, string $destinationPath): bool
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return false;
        }

        $contents = file_get_contents($sourcePath);
        if ($contents === false) {
            return false;
        }

        $image = imagecreatefromstring($contents);
        if (! $image) {
            return false;
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $quality = config('gopos.image_webp_quality', 88);
        $quality = $quality === 'lossless' && defined('IMG_WEBP_LOSSLESS')
            ? IMG_WEBP_LOSSLESS
            : max(0, min(100, (int) $quality));

        $converted = imagewebp($image, $destinationPath, $quality);
        imagedestroy($image);

        return $converted && is_file($destinationPath);
    }

    private function imageExtension(?string $contentType, string $url): string
    {
        if ($contentType && str_contains($contentType, 'png')) {
            return 'png';
        }

        if ($contentType && str_contains($contentType, 'webp')) {
            return 'webp';
        }

        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? $extension : 'jpg';
    }

    private function uniqueBaseSlug(string $value): string
    {
        return Str::slug($value) ?: 'item';
    }

    private function findItem(int $goposItemId, string $name): ?MenuItem
    {
        $item = MenuItem::query()->where('gopos_id', $goposItemId)->first()
            ?: MenuItem::query()->where('slug', $this->uniqueBaseSlug($name))->first();

        if ($item) {
            return $item;
        }

        $needle = $this->normalizedName($name);

        return MenuItem::query()
            ->get()
            ->first(fn (MenuItem $item): bool => $this->normalizedName($item->getTranslation('name', 'pl')) === $needle);
    }

    private function normalizedName(string $value): string
    {
        return Str::slug(Str::ascii(Str::lower($value))) ?: 'item';
    }

}
