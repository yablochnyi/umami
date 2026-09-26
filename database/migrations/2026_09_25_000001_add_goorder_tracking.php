<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->unsignedBigInteger('goorder_id')->nullable()->unique();
            $table->string('goorder_reference_id')->nullable();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('submission_key')->nullable()->unique();
            $table->string('tracking_token', 64)->nullable()->unique();
            $table->string('locale', 2)->default('pl');
            $table->string('goorder_id')->nullable()->unique();
            $table->text('goorder_token')->nullable();
            $table->text('goorder_checkout')->nullable();
            $table->json('goorder_quote')->nullable();
            $table->string('goorder_status')->nullable();
            $table->string('goorder_error')->nullable();
            $table->timestamp('goorder_submit_started_at')->nullable();
            $table->timestamp('goorder_synced_at')->nullable();
            $table->timestamp('expected_ready_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn([
            'submission_key', 'tracking_token', 'locale', 'goorder_id', 'goorder_token',
            'goorder_checkout', 'goorder_quote', 'goorder_status', 'goorder_error',
            'goorder_submit_started_at', 'goorder_synced_at', 'expected_ready_at',
        ]));
        Schema::table('menu_items', fn (Blueprint $table) => $table->dropColumn(['goorder_id', 'goorder_reference_id']));
    }
};
