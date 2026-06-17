<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gopos_id')->nullable()->index();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->index();
            $table->string('nip')->nullable();
            $table->string('street')->nullable();
            $table->string('building_number')->nullable();
            $table->string('apartment_number')->nullable();
            $table->json('gopos_payload')->nullable();
            $table->timestamp('gopos_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->string('status')->default('new');
            $table->string('delivery_type');
            $table->string('fulfillment_type')->default('asap');
            $table->timestamp('scheduled_at')->nullable();
            $table->string('payment_type');
            $table->boolean('wants_invoice')->default(false);
            $table->string('nip')->nullable();
            $table->string('street')->nullable();
            $table->string('building_number')->nullable();
            $table->string('apartment_number')->nullable();
            $table->text('comment')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('delivery_cost', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('free_delivery_from', 10, 2)->default(0);
            $table->decimal('minimum_delivery_amount', 10, 2)->default(0);
            $table->unsignedBigInteger('gopos_id')->nullable();
            $table->string('gopos_uid')->nullable();
            $table->string('gopos_number')->nullable();
            $table->json('gopos_payload')->nullable();
            $table->text('gopos_error')->nullable();
            $table->timestamp('gopos_sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('gopos_id')->nullable();
            $table->string('name');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('total', 10, 2)->default(0);
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('customers');
    }
};
