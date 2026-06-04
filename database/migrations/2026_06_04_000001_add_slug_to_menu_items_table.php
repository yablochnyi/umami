<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        DB::table('menu_items')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->each(function (object $item): void {
                $name = json_decode($item->name, true)['pl'] ?? 'danie';
                $baseSlug = Str::slug($name) ?: 'danie';
                $slug = $baseSlug;
                $suffix = 2;

                while (DB::table('menu_items')->where('slug', $slug)->where('id', '!=', $item->id)->exists()) {
                    $slug = $baseSlug.'-'.$suffix;
                    $suffix++;
                }

                DB::table('menu_items')->where('id', $item->id)->update(['slug' => $slug]);
            });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
