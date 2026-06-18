<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            if (! Schema::hasColumn('customers', 'city')) {
                $table->string('city')->nullable()->after('nip');
            }
        });

        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'city')) {
                $table->string('city')->nullable()->after('nip');
            }
        });

        $settings = [
            ['key' => 'delivery_opening_time', 'label' => 'Dostawa od', 'value' => '13:00', 'type' => 'time', 'sort_order' => 22],
            ['key' => 'restaurant_latitude', 'label' => 'Szerokość geograficzna restauracji', 'value' => '53.0217', 'type' => 'number', 'sort_order' => 25],
            ['key' => 'restaurant_longitude', 'label' => 'Długość geograficzna restauracji', 'value' => '18.6676', 'type' => 'number', 'sort_order' => 26],
            ['key' => 'delivery_tier_1_max_km', 'label' => 'Dostawa próg 1 do km', 'value' => '3', 'type' => 'number', 'sort_order' => 27],
            ['key' => 'delivery_tier_1_cost', 'label' => 'Dostawa do 3 km', 'value' => '9.99', 'type' => 'number', 'sort_order' => 28],
            ['key' => 'delivery_tier_2_max_km', 'label' => 'Dostawa próg 2 do km', 'value' => '8', 'type' => 'number', 'sort_order' => 29],
            ['key' => 'delivery_tier_2_cost', 'label' => 'Dostawa 3-8 km', 'value' => '14.99', 'type' => 'number', 'sort_order' => 30],
            ['key' => 'delivery_tier_3_cost', 'label' => 'Dostawa powyżej 8 km', 'value' => '24.99', 'type' => 'number', 'sort_order' => 31],
        ];

        foreach ($settings as $setting) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'group' => 'restaurant',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]),
            );
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'city')) {
                $table->dropColumn('city');
            }
        });

        Schema::table('customers', function (Blueprint $table): void {
            if (Schema::hasColumn('customers', 'city')) {
                $table->dropColumn('city');
            }
        });

        DB::table('site_settings')
            ->whereIn('key', [
                'delivery_opening_time',
                'restaurant_latitude',
                'restaurant_longitude',
                'delivery_tier_1_max_km',
                'delivery_tier_1_cost',
                'delivery_tier_2_max_km',
                'delivery_tier_2_cost',
                'delivery_tier_3_cost',
            ])
            ->delete();
    }
};
