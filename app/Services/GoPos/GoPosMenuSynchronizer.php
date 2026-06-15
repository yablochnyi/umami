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
            $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: 'jpg';
            $extension = strtolower($extension);
            $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? $extension : 'jpg';

            $path = 'umami/gopos/items/'.$goposItemId.'-'.substr(sha1($url), 0, 10).'.'.$extension;
            $absolutePath = Storage::disk('public')->path($path);

            if (! is_dir(dirname($absolutePath))) {
                mkdir(dirname($absolutePath), 0755, true);
            }

            $response = Http::timeout(60)
                ->withOptions(['sink' => $absolutePath])
                ->get($url);

            if (! $response->successful()) {
                if (is_file($absolutePath)) {
                    unlink($absolutePath);
                }

                return null;
            }

            $contentType = $response->header('Content-Type');
            if ($contentType) {
                $detectedExtension = match (true) {
                    str_contains($contentType, 'png') => 'png',
                    str_contains($contentType, 'webp') => 'webp',
                    default => null,
                };

                if ($detectedExtension && $detectedExtension !== $extension) {
                    $newPath = preg_replace('/\.[^.]+$/', '.'.$detectedExtension, $path);
                    $newAbsolutePath = Storage::disk('public')->path($newPath);
                    rename($absolutePath, $newAbsolutePath);
                    $path = $newPath;
                    $absolutePath = $newAbsolutePath;
                }
            }

            chmod($absolutePath, 0644);
            Storage::disk('public')->setVisibility($path, 'public');

            return $path;
        } catch (\Throwable) {
            return null;
        }
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
