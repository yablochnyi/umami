<?php

namespace App\Services\GoOrder;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class GoOrderModifiers
{
    public function choices(array $item, array $catalog, array $posItems = [], array $posGroups = []): array
    {
        $result = [];
        foreach ($item['modifier_groups'] ?? [] as $link) {
            $group = collect($catalog['modifier_groups'] ?? [])->firstWhere('id', $link['modifier_group_id']);
            if (! $group || ($group['status'] ?? '') !== 'ENABLED' || ($group['quantity_info']['min_permitted'] ?? null) != 1
                || ($group['quantity_info']['max_permitted'] ?? null) != 1 || ! empty($group['quantity_info_overrides']) || ! empty($item['quantity_info_overrides'])) {
                throw new RuntimeException('Unsupported GoOrder modifier rules; review required.');
            }
            $posGroupMatches = collect($posGroups)->filter(fn ($g) => Str::slug($g['name']) === Str::slug($group['name']));
            $posGroup = $posGroupMatches->count() === 1 ? $posGroupMatches->first() : null;
            $options = [];
            foreach ($group['options'] as $option) {
                $remote = collect($catalog['items'])->firstWhere('id', $option['entity_id']);
                if ($option['type'] !== 'ITEM' || ! $remote || ! empty($remote['modifier_groups'])) {
                    throw new RuntimeException('Unsupported nested GoOrder modifier.');
                }
                if ($remote['status'] !== 'ENABLED' || ! empty($remote['suspension']['sources']) || ! empty($remote['suspension']['order_types'])) {
                    continue;
                }
                $override = collect($remote['price_info_overrides'] ?? [])->first(fn ($p) => $p['context_type'] === 'ITEM' && $p['context_id'] == $item['id'])
                    ?? collect($remote['price_info_overrides'] ?? [])->first(fn ($p) => $p['context_type'] === 'MODIFIER_GROUP' && $p['context_id'] == $group['id']);
                $price = $override['price']['amount'] ?? $remote['price']['amount'];
                // The current lunch choices are included; paid extras need quote/UI support.
                if ((float) $price !== 0.0 || ! empty($remote['availability_id']) || ! empty($remote['order_types'])) {
                    throw new RuntimeException('Paid or scheduled modifiers require review.');
                }
                $posMatches = collect($posItems)->filter(fn ($p) => Str::slug($p['name']) === Str::slug($remote['name']));
                $pos = $posMatches->count() === 1 ? $posMatches->first() : null;
                $options[] = ['id' => $remote['id'], 'reference_id' => $remote['reference_id'], 'name' => $remote['name'],
                    'gopos_id' => $pos['id'] ?? null, 'gopos_group_id' => $posGroup['id'] ?? null];
            }
            if (! $options) {
                throw new RuntimeException('Required GoOrder modifier has no available options.');
            }
            $result[] = ['id' => $group['id'], 'name' => $group['name'], 'options' => $options];
        }

        return $result;
    }

    public function validate(array $choices, mixed $selected): array
    {
        if (! is_array($selected) || count($selected) !== count($choices)) {
            throw ValidationException::withMessages(['cart_json' => __('availability.choose')]);
        }
        $result = [];
        foreach ($choices as $choice) {
            $id = $selected[$choice['id']] ?? null;
            $option = collect($choice['options'])->first(fn ($o) => is_scalar($id) && (string) $o['id'] === (string) $id);
            if (! $option) {
                throw ValidationException::withMessages(['cart_json' => __('availability.choose')]);
            }
            $result[] = ['group_id' => $choice['id'], 'option' => $option];
        }

        return $result;
    }

    public function payload(array $selections): array
    {
        return array_map(fn ($s) => ['modifier_group_id' => $s['group_id'], 'selected_items' => [
            ['item_id' => $s['option']['id'], 'quantity' => 1, 'modifier_groups' => []],
        ]], $selections);
    }
}
