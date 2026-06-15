<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('gopos_id')->nullable()->unique()->after('id');
            $table->json('gopos_payload')->nullable()->after('is_active');
            $table->timestamp('gopos_synced_at')->nullable()->after('gopos_payload');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->unsignedBigInteger('gopos_id')->nullable()->unique()->after('id');
            $table->unsignedBigInteger('gopos_category_id')->nullable()->after('menu_category_id');
            $table->unsignedBigInteger('gopos_tax_id')->nullable()->after('gopos_category_id');
            $table->string('gopos_joint_id')->nullable()->after('gopos_tax_id');
            $table->json('gopos_payload')->nullable()->after('is_active');
            $table->timestamp('gopos_synced_at')->nullable()->after('gopos_payload');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropUnique(['gopos_id']);
            $table->dropColumn([
                'gopos_id',
                'gopos_category_id',
                'gopos_tax_id',
                'gopos_joint_id',
                'gopos_payload',
                'gopos_synced_at',
            ]);
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropUnique(['gopos_id']);
            $table->dropColumn(['gopos_id', 'gopos_payload', 'gopos_synced_at']);
        });
    }
};
