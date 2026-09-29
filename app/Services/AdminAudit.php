<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use App\Models\User;
use App\Support\AdminPermissions;
use App\Support\SiteSettingCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

final class AdminAudit
{
    public const FIELDS = [
        'menu_items' => ['name', 'slug', 'description', 'marketing_description', 'seo_title', 'seo_description', 'price', 'image', 'source_image', 'is_active', 'is_bestseller', 'sort_order', 'menu_category_id', 'schedule_enabled', 'schedule_hours'],
        'menu_categories' => ['name', 'slug', 'intro_text', 'seo_text', 'sort_order', 'is_active', 'goorder_import_enabled', 'schedule_enabled', 'schedule_hours'],
        'gallery_images' => ['title', 'alt', 'image', 'sort_order', 'is_active'],
        'site_texts' => ['value'],
        'site_settings' => ['value'],
        'social_links' => ['label', 'url', 'icon', 'sort_order', 'is_active'],
        'orders' => ['status'],
        'customers' => ['name', 'email', 'phone', 'nip', 'city', 'street', 'building_number', 'apartment_number'],
        'users' => ['name', 'email', 'role_id', 'admin_locale', 'password'],
        'roles' => ['name', 'permissions'],
    ];

    public function actor(User $user): array
    {
        return ['actor_id' => $user->id, 'actor_email' => $user->email, 'actor_name' => $user->name];
    }

    public function record(string $action, string $resource, array $data = [], ?array $actor = null): void
    {
        $actor ??= request()->attributes->get('admin_audit_actor');
        if (! $actor) {
            return;
        }
        // During a rolling deploy the code can precede the new table by a few seconds.
        if (! request()->attributes->has('admin_audit_ready')) {
            request()->attributes->set('admin_audit_ready', Schema::hasTable('admin_audit_logs'));
        }
        if (! request()->attributes->get('admin_audit_ready')) {
            return;
        }

        AdminAuditLog::create([
            ...$actor, 'action' => $action, 'resource' => $resource,
            ...$data, 'created_at' => now()->utc(),
        ]);
    }

    public function authentication(string $action, mixed $user): void
    {
        if (! $user instanceof User || ! request()->attributes->get('admin_audit_panel')) {
            return;
        }
        $this->record($action, 'auth', actor: $this->actor($user));
    }

    public function mutation(Model $model, string $action): void
    {
        if (! request()->attributes->get('admin_audit_actor')) {
            return;
        }
        $resource = AdminPermissions::resource($model::class);
        $old = $action === 'created' ? [] : $model->getRawOriginal();
        $new = $action === 'deleted' ? [] : $model->getAttributes();
        $changes = [];
        foreach (self::FIELDS[$resource] as $field) {
            if ($action === 'updated' && ! $model->wasChanged($field)) {
                continue;
            }
            if (! array_key_exists($field, $old) && ! array_key_exists($field, $new)) {
                continue;
            }
            $masked = $field === 'password' || $resource === 'customers'
                || ($resource === 'site_settings' && ! $this->publicSetting($model->key));
            $changes[$field] = [
                'before' => $masked ? null : $this->value($old[$field] ?? null),
                'after' => $masked ? null : $this->value($new[$field] ?? null),
                'redacted' => $masked,
            ];
        }
        if ($action === 'updated' && ! $changes) {
            return;
        }
        $this->record($action, $resource, [
            'subject_id' => (string) $model->getKey(),
            'subject_label' => $this->label($model, $resource),
            'changes' => $changes,
        ]);
    }

    private function publicSetting(string $key): bool
    {
        return isset(SiteSettingCatalog::FIELDS[$key])
            || in_array($key, ['opening_time', 'closing_time', 'delivery_opening_time', 'minimum_delivery_amount', 'free_delivery_from', 'restaurant_latitude', 'restaurant_longitude'], true)
            || preg_match('/^delivery_tier_[123]_(cost|max_km|streets)$/', $key) === 1;
    }

    private function value(mixed $value): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $value;
    }

    public function label(Model $model, string $resource): string
    {
        if (in_array($resource, ['site_texts', 'site_settings'], true)) {
            return (string) $model->key;
        }
        if ($resource === 'customers') {
            return '#'.$model->getKey();
        }
        $attributes = $model->getAttributes();
        $value = $this->value($attributes['name'] ?? $attributes['title'] ?? $attributes['label'] ?? $attributes['number'] ?? '#'.$model->getKey());
        if (is_array($value)) {
            $value = $value['pl'] ?? $value['uk'] ?? $value['en'] ?? '#'.$model->getKey();
        }

        return mb_substr((string) $value, 0, 255);
    }
}
