<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Models\User;
use App\Services\AdminAudit;
use App\Support\AdminPermissions;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditAdminActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $audit = app(AdminAudit::class);
        // Livewire replays this middleware on a verified route snapshot, before actions run.
        request()->attributes->set('admin_audit_panel', true);
        if ($user = $request->user()) {
            request()->attributes->set('admin_audit_actor', $audit->actor($user));
        }
        $response = $next($request);
        if (request()->isMethod('GET') && $response->getStatusCode() === 200 && $request->user()) {
            $name = $request->route()?->getName() ?? '';
            if (preg_match('/^filament\.admin\.resources\.([a-z-]+)\.([a-z]+)$/', $name, $parts)) {
                $resource = str_replace('-', '_', $parts[1]);
                $record = $request->route('record');
                $modelClass = [...AdminPermissions::RESOURCES, 'users' => User::class, 'roles' => Role::class][$resource] ?? null;
                $subject = $record instanceof Model ? $record : ($record && $modelClass ? $modelClass::find($record) : null);
                $audit->record('viewed', $resource, [
                    'subject_id' => $record ? (string) ($subject?->getKey() ?? $record) : null,
                    'subject_label' => $subject ? $audit->label($subject, $resource) : null,
                    'page' => $parts[2],
                ]);
            } elseif (str_starts_with($name, 'filament.admin.pages.')) {
                $audit->record('viewed', match (substr($name, strlen('filament.admin.pages.'))) {
                    'dashboard' => 'dashboard',
                    'ustawienia-strony' => 'restaurant_settings',
                    'sales-analytics' => 'sales_analytics',
                    'activity-log' => 'activity_log',
                    default => 'profile',
                }, ['page' => 'index']);
            } elseif ($name === 'filament.admin.auth.profile') {
                $audit->record('viewed', 'profile', ['page' => 'edit']);
            }
        }

        return $response;
    }
}
