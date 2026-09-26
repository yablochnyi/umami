<?php

namespace App\Console\Commands;

use App\Models\MenuItem;
use App\Services\GoOrder\GoOrderClient;
use Illuminate\Console\Command;

class MapGoOrderMenuCommand extends Command
{
    protected $signature = 'goorder:map-menu {--apply : Save unique exact-name matches without changing prices}';

    protected $description = 'Check the GoOrder catalog and map local items (read-only unless --apply)';

    public function handle(GoOrderClient $client): int
    {
        $normalize = fn (string $name): string => mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name)));
        $remote = collect($client->menu()['items'] ?? [])->groupBy(fn ($item) => $normalize($item['name']));
        $local = MenuItem::query()->where('is_active', true)->get();
        $localNames = $local->groupBy(fn ($item) => $normalize($item->getTranslation('name', 'pl', false)));
        $rows = [];
        foreach ($local as $item) {
            $name = $item->getTranslation('name', 'pl', false);
            $matches = $remote->get($normalize($name), collect());
            $match = $matches->count() === 1 && $localNames[$normalize($name)]->count() === 1 ? $matches->first() : null;
            $status = 'NO UNIQUE MATCH';
            if ($match) {
                $status = ! empty($match['modifier_groups']) ? 'MODIFIERS: setup required' : 'MATCH';
                if ($item->goorder_id && ($item->goorder_id != $match['id'] || $item->goorder_reference_id !== $match['reference_id'])) {
                    $status = 'IDENTITY CHANGED: review manually';
                } elseif ($this->option('apply')) {
                    $item->update(['goorder_id' => $match['id'], 'goorder_reference_id' => $match['reference_id']]);
                }
            }
            $rows[] = [$item->id, $name, $match['id'] ?? '-', $item->price, $match['price']['amount'] ?? '-', $status];
        }
        $this->table(['Local ID', 'Name', 'GoOrder ID', 'Site price', 'GoOrder price', 'Check'], $rows);
        $this->info($this->option('apply') ? 'Mappings saved; prices were not changed.' : 'Read-only. Review matches before running with --apply.');

        return self::SUCCESS;
    }
}
