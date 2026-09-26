<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\GoOrder\GoOrderService;
use Illuminate\Console\Command;

class SyncGoOrderStatusCommand extends Command
{
    protected $signature = 'goorder:sync-status';

    protected $description = 'Refresh pending GoOrder orders without resending them';

    public function handle(GoOrderService $service): int
    {
        Order::query()->whereNotNull('goorder_id')
            ->whereIn('status', ['submitting_goorder', 'submission_uncertain', 'waiting_staff', 'accepted', 'ready', 'delivering'])
            ->where('created_at', '>=', now()->subDays(14))
            ->eachById(fn (Order $order) => $service->sync($order));

        return self::SUCCESS;
    }
}
