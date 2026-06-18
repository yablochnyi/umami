<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            [
                'key' => 'delivery_tier_1_streets',
                'label' => 'Ulice zawsze w strefie 1',
                'value' => "Generała Karola Kniaziewicza\nWojciecha Korfantego\nSzuwarów\nAkacjowa",
                'sort_order' => 38,
            ],
            [
                'key' => 'delivery_tier_2_streets',
                'label' => 'Ulice zawsze w strefie 2',
                'value' => '',
                'sort_order' => 39,
            ],
            [
                'key' => 'delivery_tier_3_streets',
                'label' => 'Ulice zawsze w strefie 3',
                'value' => '',
                'sort_order' => 40,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'group' => 'restaurant',
                    'type' => 'textarea',
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
                'delivery_tier_1_streets',
                'delivery_tier_2_streets',
                'delivery_tier_3_streets',
            ])
            ->delete();
    }
};
