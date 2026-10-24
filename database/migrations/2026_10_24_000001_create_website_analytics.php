<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_visits', function (Blueprint $table): void {
            $table->id();
            $table->char('visitor_hash', 64);
            $table->dateTime('started_at');
            $table->dateTime('last_seen_at')->index();
            $table->string('source', 100);
            $table->string('campaign', 100)->nullable();
            $table->string('device', 16);
            $table->index(['visitor_hash', 'last_seen_at']);
        });
        Schema::create('website_events', function (Blueprint $table): void {
            $table->id();
            $table->char('event_key', 64)->unique();
            $table->foreignId('website_visit_id')->constrained()->cascadeOnDelete();
            $table->dateTime('occurred_at');
            $table->date('business_date')->index();
            $table->unsignedTinyInteger('hour');
            $table->string('name', 24);
            $table->string('path', 255);
            $table->foreignId('menu_item_id')->nullable()->constrained()->nullOnDelete();
            $table->index(['business_date', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_events');
        Schema::dropIfExists('website_visits');
    }
};
