<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['key' => 'delivery_tier_1_zone_id', 'label' => 'GoPOS ID strefy 1', 'value' => '2', 'sort_order' => 32],
            ['key' => 'delivery_tier_1_zone_name', 'label' => 'GoPOS nazwa strefy 1', 'value' => 'Strefa 1', 'sort_order' => 33],
            ['key' => 'delivery_tier_2_zone_id', 'label' => 'GoPOS ID strefy 2', 'value' => '3', 'sort_order' => 34],
            ['key' => 'delivery_tier_2_zone_name', 'label' => 'GoPOS nazwa strefy 2', 'value' => 'Strefa 2', 'sort_order' => 35],
            ['key' => 'delivery_tier_3_zone_id', 'label' => 'GoPOS ID strefy 3', 'value' => '4', 'sort_order' => 36],
            ['key' => 'delivery_tier_3_zone_name', 'label' => 'GoPOS nazwa strefy 3', 'value' => 'Strefa 3', 'sort_order' => 37],
        ];

        foreach ($settings as $setting) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'group' => 'restaurant',
                    'type' => 'text',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]),
            );
        }
    }

    public function down(): void
    {
        DB::table('site_settings')
            ->whereIn('key', [
                'delivery_tier_1_zone_id',
                'delivery_tier_1_zone_name',
                'delivery_tier_2_zone_id',
                'delivery_tier_2_zone_name',
                'delivery_tier_3_zone_id',
                'delivery_tier_3_zone_name',
            ])
            ->delete();
    }
};
