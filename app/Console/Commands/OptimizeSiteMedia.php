<?php

namespace App\Console\Commands;

use App\Models\GalleryImage;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use App\Support\OptimizedMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class OptimizeSiteMedia extends Command
{
    protected $signature = 'site:optimize-media {--ffmpeg=ffmpeg : FFmpeg executable} {--images-only : Skip videos}';

    protected $description = 'Create smaller public image and hero video copies without changing original media or database records';

    public function handle(OptimizedMedia $media): int
    {
        if (! function_exists('imagewebp')) {
            $this->error('PHP GD with WebP support is required.');

            return self::FAILURE;
        }

        $settings = SiteSetting::query()->pluck('value', 'key');
        $jobs = [];
        foreach (MenuItem::query()->pluck('image') as $path) {
            $jobs[] = [$path, 'card'];
            $jobs[] = [$path, 'image'];
        }
        foreach (GalleryImage::query()->pluck('image') as $path) {
            $jobs[] = [$path, 'image'];
        }
        foreach (['hero_poster' => 'umami/res1.png', 'about_image' => 'umami/res8.png'] as $key => $fallback) {
            $jobs[] = [$settings[$key] ?? $fallback, 'image'];
        }
        $jobs[] = [$settings['hero_poster'] ?? 'umami/res1.png', 'poster-mobile'];
        if (! $this->option('images-only')) {
            foreach (['desktop', 'mobile'] as $device) {
                $jobs[] = [$settings['hero_video_'.$device] ?? $settings['hero_video'] ?? 'umami/UMAMI.MP4', 'video-'.$device];
            }
        }

        $disk = Storage::disk('public');
        $disk->makeDirectory('umami/optimized');
        $seen = [];
        $created = $skipped = $failed = $saved = 0;
        foreach ($jobs as [$path, $profile]) {
            $source = $media->source($path);
            if ($source === null) {
                $skipped++;

                continue;
            }
            $destination = $media->derivative($source, $profile);
            if (isset($seen[$destination]) || $disk->exists($destination)) {
                $skipped++;

                continue;
            }
            $seen[$destination] = true;
            $input = $disk->path($source);
            $output = $disk->path($destination);
            $temporary = $output.'.'.bin2hex(random_bytes(6)).'.tmp';
            try {
                if (str_starts_with($profile, 'video-')) {
                    $width = $profile === 'video-mobile' ? 540 : 1280;
                    $process = new Process([
                        $this->option('ffmpeg'), '-nostdin', '-hide_banner', '-loglevel', 'error',
                        '-i', $input, '-map', '0:v:0', '-an',
                        '-vf', "scale='min({$width},iw)':-2:flags=lanczos,fps=24",
                        '-c:v', 'libx264', '-preset', 'slow', '-crf', '27', '-pix_fmt', 'yuv420p',
                        '-movflags', '+faststart', '-map_metadata', '-1', '-f', 'mp4', $temporary,
                    ]);
                    $process->setTimeout(600)->mustRun();
                } else {
                    $this->image($input, $temporary, OptimizedMedia::IMAGE_WIDTHS[$profile]);
                }
                if (! is_file($temporary) || filesize($temporary) === 0) {
                    throw new \RuntimeException('Empty optimized file');
                }
                // Keep the existing asset if encoding would increase its size.
                if (filesize($temporary) >= filesize($input)) {
                    unlink($temporary);
                    $skipped++;

                    continue;
                }
                $saved += filesize($input) - filesize($temporary);
                chmod($temporary, 0644);
                if (! rename($temporary, $output)) {
                    throw new \RuntimeException('Unable to publish optimized file');
                }
                $created++;
                $this->line($profile.': '.$source);
            } catch (\Throwable $error) {
                if (is_file($temporary)) {
                    unlink($temporary);
                }
                $this->warn($source.': '.$error->getMessage());
                $failed++;
            }
        }
        $this->info("Created: {$created}; skipped: {$skipped}; failed: {$failed}; saved across variants: ".round($saved / 1048576, 2).' MiB');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function image(string $source, string $output, int $maxWidth): void
    {
        $info = @getimagesize($source);
        if (! $info || $info[0] * $info[1] > 24000000 || ! in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new \RuntimeException('Unsupported or oversized image');
        }
        // GD does not apply EXIF orientation; retain such JPEGs rather than rotate them incorrectly.
        if ($info['mime'] === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($source);
            if (($exif['Orientation'] ?? 1) !== 1) {
                throw new \RuntimeException('Image requires orientation normalization');
            }
        }
        $original = @imagecreatefromstring(file_get_contents($source));
        if (! $original) {
            throw new \RuntimeException('Cannot decode image');
        }
        $width = min($maxWidth, $info[0]);
        $height = max(1, (int) round($info[1] * $width / $info[0]));
        $resized = imagecreatetruecolor($width, $height);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $original, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
        try {
            if (! imagewebp($resized, $output, 78)) {
                throw new \RuntimeException('Cannot encode WebP');
            }
        } finally {
            imagedestroy($original);
            imagedestroy($resized);
        }
    }
}
