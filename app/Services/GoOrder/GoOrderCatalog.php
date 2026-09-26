<?php

namespace App\Services\GoOrder;

use RuntimeException;

class GoOrderCatalog
{
    public const DAYS = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'];

    public function parse(array $data): array
    {
        foreach (['menus', 'categories', 'items', 'item_groups', 'availabilities', 'modifier_groups'] as $key) {
            if (! isset($data[$key]) || ! is_array($data[$key]) || ! array_is_list($data[$key])) {
                throw new RuntimeException('Incomplete GoOrder catalog: '.$key);
            }
        }
        $items = $this->index($data['items']);
        $categories = $this->index($data['categories']);
        $groups = $this->index($data['item_groups']);
        $availability = $this->index($data['availabilities']);
        $menus = collect($data['menus'])->where('reference_id', config('goorder.menu_reference'))->values();
        if ($menus->count() !== 1) {
            throw new RuntimeException('Configured GoOrder menu is missing or ambiguous.');
        }
        $menu = $menus->first();
        $result = [];
        $references = [];
        foreach ($menu['categories'] ?? [] as $entry) {
            $category = $categories[$entry['category_id']] ?? throw new RuntimeException('Missing GoOrder category.');
            if (empty($category['reference_id']) || ! isset($category['entities'], $category['status'])) {
                throw new RuntimeException('Invalid GoOrder category.');
            }
            $rows = [];
            foreach ($category['entities'] as $entity) {
                $ids = match ($entity['type']) {
                    'ITEM' => [$entity['entity_id']],
                    'ITEM_GROUP' => array_column(($groups[$entity['entity_id']] ?? throw new RuntimeException('Missing item group.'))['items'], 'item_id'),
                    default => throw new RuntimeException('Unsupported GoOrder menu entity.'),
                };
                foreach ($ids as $id) {
                    $item = $items[$id] ?? throw new RuntimeException('Missing GoOrder menu item.');
                    if (empty($item['reference_id']) || empty($item['name']) || ! isset($item['status'])
                        || ! is_numeric($item['price']['amount'] ?? null) || ($item['price']['currency'] ?? '') !== 'PLN') {
                        throw new RuntimeException('Invalid GoOrder item: '.$id);
                    }
                    if (isset($references[$item['reference_id']]) && $references[$item['reference_id']] !== $id) {
                        throw new RuntimeException('Duplicate GoOrder item reference.');
                    }
                    $references[$item['reference_id']] = $id;
                    $rules = [];
                    foreach ([$menu, $category, $item] as $owner) {
                        if (isset($owner['availability_id'])) {
                            $rule = $availability[$owner['availability_id']] ?? throw new RuntimeException('Missing GoOrder availability.');
                            $rules[] = $this->schedule($rule);
                        }
                    }
                    $rows[$id] = ['item' => $item, 'rules' => $rules, 'position' => $entity['position'] ?? 0];
                }
            }
            $result[] = ['category' => $category, 'position' => $entry['position'] ?? 0, 'items' => $rows,
                'published' => ($menu['status'] ?? 'ENABLED') === 'ENABLED' && $category['status'] === 'ENABLED'];
        }
        if (! $result || ! $references) {
            throw new RuntimeException('Empty GoOrder menu: synchronization stopped without changes.');
        }

        return $result;
    }

    private function index(array $rows): array
    {
        $indexed = [];
        foreach ($rows as $row) {
            if (! isset($row['id']) || isset($indexed[$row['id']])) {
                throw new RuntimeException('Invalid or duplicate GoOrder ID.');
            }
            $indexed[$row['id']] = $row;
        }

        return $indexed;
    }

    private function schedule(array $rule): array
    {
        if (! empty($rule['dates'])) {
            throw new RuntimeException('GoOrder date-specific availability requires review before synchronization.');
        }
        $hours = [];
        foreach ($rule['hours'] ?? [] as $window) {
            $from = array_search($window['day_from'], self::DAYS, true);
            $to = array_search($window['day_to'], self::DAYS, true);
            if ($from === false || $to === false || ! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $window['hour_from'])
                || ! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $window['hour_to'])) {
                throw new RuntimeException('Invalid GoOrder weekly schedule.');
            }
            $days = [];
            for ($day = $from; ; $day = ($day + 1) % 7) {
                $days[] = $day + 1;
                if ($day === $to) {
                    break;
                }
            }
            $hours[] = ['days' => $days, 'from' => $window['hour_from'], 'to' => $window['hour_to']];
        }

        return ['name' => $rule['name'] ?? '', 'enabled' => ($rule['status'] ?? '') === 'ENABLED', 'hours' => $hours];
    }
}
