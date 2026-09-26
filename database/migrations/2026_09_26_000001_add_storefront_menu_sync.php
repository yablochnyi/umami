<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('goorder_id')->nullable()->unique();
            $table->string('goorder_reference_id')->nullable()->unique();
            $table->boolean('goorder_import_enabled')->default(true);
        });
        foreach (['menu_categories', 'menu_items'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->boolean('goorder_published')->nullable()->index();
                $table->timestamp('goorder_synced_at')->nullable();
                $table->boolean('schedule_enabled')->default(false);
                $table->json('schedule_hours')->nullable();
            });
        }
        Schema::table('menu_items', function (Blueprint $table) {
            $table->json('goorder_rules')->nullable();
            $table->json('goorder_payload')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', fn (Blueprint $table) => $table->dropColumn(['goorder_rules', 'goorder_payload']));
        foreach (['menu_categories', 'menu_items'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn(['goorder_published', 'goorder_synced_at', 'schedule_enabled', 'schedule_hours']));
        }
        Schema::table('menu_categories', fn (Blueprint $table) => $table->dropColumn(['goorder_id', 'goorder_reference_id', 'goorder_import_enabled']));
    }
};
