<?php

namespace App\Observers;

use App\Services\AdminAudit;
use Illuminate\Database\Eloquent\Model;

class AdminAuditObserver
{
    public function created(Model $model): void
    {
        app(AdminAudit::class)->mutation($model, 'created');
    }

    public function updated(Model $model): void
    {
        app(AdminAudit::class)->mutation($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        app(AdminAudit::class)->mutation($model, 'deleted');
    }
}
