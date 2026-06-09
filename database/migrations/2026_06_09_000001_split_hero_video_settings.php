<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $oldHeroVideo = DB::table('site_settings')->where('key', 'hero_video')->first();
        $desktopVideo = DB::table('site_settings')->where('key', 'hero_video_desktop')->first();

        if ($oldHeroVideo && ! $desktopVideo) {
            DB::table('site_settings')
                ->where('key', 'hero_video')
                ->update([
                    'key' => 'hero_video_desktop',
                    'label' => 'Hero video desktop',
                    'type' => 'video',
                    'sort_order' => 5,
                    'updated_at' => now(),
                ]);
        } elseif ($oldHeroVideo) {
            DB::table('site_settings')->where('key', 'hero_video')->delete();
        }

        $desktopValue = DB::table('site_settings')
            ->where('key', 'hero_video_desktop')
            ->value('value') ?: 'umami/UMAMI.MP4';

        DB::table('site_settings')->updateOrInsert(
            ['key' => 'hero_video_mobile'],
            [
                'group' => 'hero',
                'label' => 'Hero video mobile',
                'value' => $desktopValue,
                'type' => 'video',
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        DB::table('site_settings')
            ->where('key', 'hero_poster')
            ->update(['sort_order' => 7, 'updated_at' => now()]);
    }

    public function down(): void
    {
        $desktopVideo = DB::table('site_settings')->where('key', 'hero_video_desktop')->first();

        if ($desktopVideo) {
            DB::table('site_settings')
                ->where('key', 'hero_video_desktop')
                ->update([
                    'key' => 'hero_video',
                    'label' => 'Hero video',
                    'sort_order' => 5,
                    'updated_at' => now(),
                ]);
        }

        DB::table('site_settings')->where('key', 'hero_video_mobile')->delete();
        DB::table('site_settings')
            ->where('key', 'hero_poster')
            ->update(['sort_order' => 6, 'updated_at' => now()]);
    }
};
