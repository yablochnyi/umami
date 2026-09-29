<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_orders', function (Blueprint $table): void {
            $table->id();
            $table->string('organization_id', 50);
            $table->unsignedBigInteger('remote_id');
            $table->string('number', 100);
            $table->dateTime('ordered_at');
            $table->date('business_date')->index();
            $table->unsignedTinyInteger('hour');
            $table->unsignedTinyInteger('weekday');
            $table->string('source', 100)->index();
            $table->string('status', 40)->index();
            $table->string('payment_status', 40);
            $table->string('order_type', 40);
            $table->string('currency', 3);
            $table->bigInteger('total_cents');
            $table->bigInteger('paid_cents');
            $table->bigInteger('net_cents')->nullable();
            $table->json('payments');
            $table->timestamp('synced_at');
            $table->unique(['organization_id', 'remote_id']);
        });
        Schema::create('analytics_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('analytics_order_id')->constrained()->cascadeOnDelete();
            $table->string('remote_item_id', 100)->nullable();
            $table->string('name');
            $table->decimal('quantity', 12, 3);
            $table->bigInteger('total_cents');
            $table->index('remote_item_id');
        });
        Schema::create('analytics_sync_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('status', 20);
            $table->date('from_date');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('orders_count')->default(0);
            $table->string('error')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_sync_runs');
        Schema::dropIfExists('analytics_items');
        Schema::dropIfExists('analytics_orders');
    }
};
