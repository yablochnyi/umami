<?php

namespace App\Services\GoOrder;

use App\Models\MenuItem;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class MenuAvailability
{
    public function snapshot(?CarbonImmutable $at = null, ?string $type = null): array
    {
        return [
            'items' => MenuItem::with('category')->get()->mapWithKeys(fn ($item) => [$item->id => [
                'available' => $this->check($item, $at, $type),
                'label' => $this->label($item),
                'choices' => collect($item->goorder_payload['choices'] ?? [])->map(fn ($group) => [
                    'id' => $group['id'], 'name' => $group['name'],
                    'options' => array_map(fn ($o) => ['id' => $o['id'], 'name' => $o['name']], $group['options']),
                ])->all(),
            ]])->all(),
            'copy' => __('availability'),
        ];
    }

    public function check(MenuItem $item, ?CarbonInterface $at = null, ?string $type = null): bool
    {
        if (! $item->is_active || $item->goorder_published === false || ! $item->category?->is_active
            || $item->category->goorder_published === false || ! $item->category->goorder_import_enabled) {
            return false;
        }
        $at = $at ? CarbonImmutable::instance($at)->timezone('Europe/Warsaw') : CarbonImmutable::now('Europe/Warsaw');
        $payload = $item->goorder_payload ?? [];
        $types = $type ? [$type] : ['PICK_UP', 'DELIVERY'];
        $allowed = array_filter($types, fn ($candidate) => (empty($payload['order_types']) || in_array($candidate, $payload['order_types'], true))
            && ! in_array($candidate, $payload['suspension']['order_types'] ?? [], true));
        if (! $allowed || ! empty($payload['suspension']['sources'])) {
            return false;
        }
        foreach ($item->goorder_rules ?? [] as $rule) {
            if (! $rule['enabled'] || ! $this->within($rule['hours'], $at)) {
                return false;
            }
        }
        foreach ([$item->category, $item] as $owner) {
            if ($owner->schedule_enabled && ! $this->within($owner->schedule_hours ?? [], $at)) {
                return false;
            }
        }

        return true;
    }

    public function within(array $hours, CarbonInterface $at): bool
    {
        $time = $at->format('H:i');
        $day = $at->isoWeekday();
        foreach ($hours as $row) {
            $days = array_map('intval', $row['days'] ?? []);
            $from = $row['from'] ?? '';
            $to = $row['to'] ?? '';
            if (! $from || ! $to || $from === $to) {
                continue;
            }
            if ($from < $to && in_array($day, $days, true) && $time >= $from && $time < $to) {
                return true;
            }
            if ($from > $to && ((in_array($day, $days, true) && $time >= $from)
                || (in_array($day === 1 ? 7 : $day - 1, $days, true) && $time < $to))) {
                return true;
            }
        }

        return false;
    }

    public function label(MenuItem $item): string
    {
        $labels = [];
        foreach ($item->goorder_rules ?? [] as $rule) {
            if ($rule['name'] === 'Menu GoOrder') {
                continue;
            }
            $labels = array_merge($labels, $this->hourLabels($rule['hours']));
        }
        foreach ([$item->category, $item] as $owner) {
            if ($owner?->schedule_enabled) {
                $labels = array_merge($labels, $this->hourLabels($owner->schedule_hours ?? []));
            }
        }

        return implode('; ', array_unique($labels));
    }

    private function hourLabels(array $hours): array
    {
        return collect($hours)->groupBy(fn ($row) => $row['from'].'-'.$row['to'])->map(function ($rows, $time) {
            $days = $rows->pluck('days')->flatten()->unique()->sort()->map(fn ($day) => __('availability.days.'.$day));

            return $days->implode(', ').' '.$time;
        })->values()->all();
    }
}
