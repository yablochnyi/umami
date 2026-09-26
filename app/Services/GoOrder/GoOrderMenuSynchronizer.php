<?php

namespace App\Services\GoOrder;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class GoOrderMenuSynchronizer
{
    public function sync(array $data, array $posItems = [], bool $dryRun = false, bool $allowLargeRemoval = false, array $posGroups = []): array
    {
        $catalog = app(GoOrderCatalog::class)->parse($data);
        $posByReference = collect($posItems)->keyBy('joint_id');
        $stats = ['categories_created' => 0, 'items_created' => 0, 'existing_preserved' => 0, 'items_unpublished' => 0, 'categories_unpublished' => 0];
        DB::beginTransaction();
        try {
            $local = MenuItem::all();
            $initialPublication = $local->mapWithKeys(fn ($item) => [$item->id => $item->goorder_published]);
            $categories = MenuCategory::all();
            $seenItems = [];
            $seenCategories = [];
            $newItems = [];
            foreach ($catalog as $section) {
                $remoteCategory = $section['category'];
                $category = $categories->firstWhere('goorder_reference_id', $remoteCategory['reference_id']);
                if (! $category) {
                    $matches = $categories->filter(fn ($c) => ! $c->goorder_reference_id && $this->normalized($c->getTranslation('name', 'pl')) === $this->normalized($remoteCategory['name']));
                    if ($matches->count() > 1) {
                        throw new RuntimeException('Ambiguous local category: '.$remoteCategory['name']);
                    }
                    $category = $matches->first();
                }
                if (! $category) {
                    $category = MenuCategory::create([
                        'name' => $this->translations($remoteCategory, 'name'),
                        'slug' => $this->slug(MenuCategory::class, $remoteCategory['name']),
                        'sort_order' => $section['position'], 'is_active' => true, 'goorder_import_enabled' => true,
                    ]);
                    $categories->push($category);
                    $stats['categories_created']++;
                }
                $category->update([
                    'goorder_id' => $remoteCategory['id'], 'goorder_reference_id' => $remoteCategory['reference_id'],
                    'goorder_published' => $section['published'], 'goorder_synced_at' => now(),
                ]);
                $seenCategories[] = $category->id;
                if (! $category->goorder_import_enabled) {
                    continue;
                }
                foreach ($section['items'] as $row) {
                    $remote = $row['item'];
                    $matches = $local->filter(fn ($i) => $i->goorder_reference_id === $remote['reference_id']
                        || (! $i->goorder_reference_id && $i->gopos_joint_id === $remote['reference_id']));
                    if ($matches->isEmpty()) {
                        $matches = $local->filter(fn ($i) => ! $i->goorder_reference_id && $this->normalized($i->getTranslation('name', 'pl')) === $this->normalized($remote['name']));
                    }
                    if ($matches->count() > 1) {
                        throw new RuntimeException('Ambiguous local item: '.$remote['name']);
                    }
                    $item = $matches->first();
                    $published = $section['published'] && $remote['status'] === 'ENABLED';
                    if (! $item) {
                        if (! $published) {
                            continue;
                        }
                        $posMatches = collect($posItems)->filter(fn ($p) => $this->normalized($p['name']) === $this->normalized($remote['name']));
                        $pos = $posByReference->get($remote['reference_id']) ?? ($posMatches->count() === 1 ? $posMatches->first() : null);
                        if (! config('goorder.enabled') && ! $pos) {
                            throw new RuntimeException('Missing GoPOS routing ID for new dish: '.$remote['name']);
                        }
                        $item = MenuItem::create([
                            'menu_category_id' => $category->id,
                            'name' => $this->translations($remote, 'name'),
                            'description' => $this->translations($remote, 'description'),
                            'slug' => $this->slug(MenuItem::class, $remote['name']),
                            'price' => rtrim(rtrim(number_format((float) $remote['price']['amount'], 2, ',', ''), '0'), ',').' zł',
                            'source_image' => $remote['image_link']['default'] ?? null,
                            'is_active' => true, 'sort_order' => $row['position'],
                            'gopos_id' => $pos['id'] ?? null, 'gopos_joint_id' => $pos['joint_id'] ?? null,
                            'gopos_category_id' => $pos['category_id'] ?? null, 'gopos_tax_id' => $pos['tax_id'] ?? null,
                            'gopos_payload' => $pos,
                        ]);
                        $local->push($item);
                        $newItems[] = $item;
                        $stats['items_created']++;
                    } elseif (! in_array($item->id, $seenItems, true)) {
                        $stats['existing_preserved']++;
                    }
                    $item->update([
                        'goorder_id' => $remote['id'], 'goorder_reference_id' => $remote['reference_id'],
                        'goorder_published' => $published, 'goorder_synced_at' => now(),
                        'goorder_rules' => $row['rules'],
                        'goorder_payload' => array_merge($remote, ['choices' => app(GoOrderModifiers::class)->choices($remote, $data, $posItems, $posGroups)]),
                    ]);
                    $seenItems[] = $item->id;
                }
            }
            $missing = $local->filter(fn ($i) => ! in_array($i->id, $seenItems, true) && $i->goorder_published !== false);
            $baseline = max(1, $local->count() - $stats['items_created']);
            $newlyDisabled = $local->filter(fn ($i) => in_array($i->id, $seenItems, true) && $i->goorder_published === false && $initialPublication->get($i->id) !== false)->count();
            if (! $allowLargeRemoval && ($missing->count() + $newlyDisabled) / $baseline > 0.25) {
                throw new RuntimeException('More than 25% of the local menu would disappear. Review the export and use --allow-large-removal only for an intentional change.');
            }
            $stats['items_unpublished'] += $newlyDisabled;
            foreach ($missing as $item) {
                $item->update(['goorder_published' => false, 'goorder_synced_at' => now()]);
                $stats['items_unpublished']++;
            }
            foreach ($categories->whereNotIn('id', $seenCategories) as $category) {
                if ($category->goorder_published !== false) {
                    $category->update(['goorder_published' => false, 'goorder_synced_at' => now()]);
                    $stats['categories_unpublished']++;
                }
            }
            if ($dryRun) {
                DB::rollBack();
            } else {
                foreach ($newItems as $item) {
                    if ($item->source_image) {
                        $item->update(['image' => $this->downloadImage($item)]);
                    }
                }
                DB::commit();
            }
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return $stats;
    }

    private function normalized(string $name): string
    {
        return Str::slug(Str::ascii(Str::lower($name)));
    }

    private function translations(array $remote, string $field): array
    {
        $result = array_fill_keys(['pl', 'uk', 'en'], $remote[$field] ?? '');
        foreach ($remote['translations'] ?? [] as $translation) {
            if (isset($result[$translation['locale'] ?? '']) && filled($translation[$field] ?? null)) {
                $result[$translation['locale']] = $translation[$field];
            }
        }

        return $result;
    }

    private function slug(string $model, string $name): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        for ($suffix = 2; $model::where('slug', $slug)->exists(); $suffix++) {
            $slug = $base.'-'.$suffix;
        }

        return $slug;
    }

    private function downloadImage(MenuItem $item): string
    {
        $url = $item->source_image;
        $host = parse_url($url, PHP_URL_HOST) ?: '';
        if (parse_url($url, PHP_URL_SCHEME) !== 'https' || (! str_ends_with($host, '.cloudfront.net') && ! str_ends_with($host, '.amazonaws.com'))) {
            throw new RuntimeException('Unexpected GoOrder image host.');
        }
        $response = Http::connectTimeout(5)->timeout(30)->withoutRedirecting()->get($url)->throw();
        $contents = $response->body();
        if (strlen($contents) > 10 * 1024 * 1024) {
            throw new RuntimeException('GoOrder image exceeds 10 MB.');
        }
        $extension = match ((getimagesizefromstring($contents) ?: [])['mime'] ?? '') {
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp',
            default => throw new RuntimeException('Invalid GoOrder image format.'),
        };
        $path = 'umami/goorder/'.$item->goorder_id.'-'.substr(sha1($url), 0, 12).'.'.$extension;
        if (! Storage::disk('public')->put($path, $contents, 'public')) {
            throw new RuntimeException('Could not save GoOrder image.');
        }

        return $path;
    }
}
