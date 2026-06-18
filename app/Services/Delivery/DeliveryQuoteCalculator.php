<?php

namespace App\Services\Delivery;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DeliveryQuoteCalculator
{
    private const FALLBACK_DISTANCES = [
        'Toruń' => 3.0,
        'Lubicz Dolny' => 9.5,
        'Lubicz Górny' => 11.0,
        'Przysiek' => 8.5,
        'Kaszczorek' => 10.0,
    ];

    public function quote(array $settings, ?string $city, ?string $street = null, ?string $building = null): array
    {
        $city = $this->normalizeCity($city);
        $distance = $this->distance($settings, $city, $street, $building);

        return [
            'city' => $city,
            'distance_km' => $distance,
            'cost' => $this->costForDistance($settings, $distance),
        ];
    }

    public function costForDistance(array $settings, ?float $distance): float
    {
        $distance ??= (float) ($settings['deliveryTier1MaxKm'] ?? 3);

        if ($distance <= (float) ($settings['deliveryTier1MaxKm'] ?? 3)) {
            return (float) ($settings['deliveryTier1Cost'] ?? 9.99);
        }

        if ($distance <= (float) ($settings['deliveryTier2MaxKm'] ?? 8)) {
            return (float) ($settings['deliveryTier2Cost'] ?? 14.99);
        }

        return (float) ($settings['deliveryTier3Cost'] ?? 24.99);
    }

    private function distance(array $settings, string $city, ?string $street, ?string $building): ?float
    {
        if (! filled($street)) {
            return self::FALLBACK_DISTANCES[$city] ?? null;
        }

        $destination = $this->geocode($city, $street, $building);
        if (! $destination) {
            return self::FALLBACK_DISTANCES[$city] ?? null;
        }

        return $this->haversine(
            (float) ($settings['restaurantLatitude'] ?? 53.0217),
            (float) ($settings['restaurantLongitude'] ?? 18.6676),
            $destination['lat'],
            $destination['lng'],
        );
    }

    private function geocode(string $city, string $street, ?string $building): ?array
    {
        $query = trim(implode(' ', array_filter([$street, $building, $city, 'Polska'])));
        $cacheKey = 'delivery-geocode:'.md5($query);

        return Cache::remember($cacheKey, now()->addMonth(), function () use ($query): ?array {
            try {
                $response = Http::timeout(3)
                    ->withHeaders([
                        'User-Agent' => 'UmamiSushiFood/1.0 (https://umamisushifood.pl)',
                    ])
                    ->get('https://nominatim.openstreetmap.org/search', [
                        'format' => 'jsonv2',
                        'limit' => 1,
                        'countrycodes' => 'pl',
                        'q' => $query,
                    ]);

                if (! $response->successful()) {
                    return null;
                }

                $item = collect($response->json())->first();
                if (! is_array($item) || ! isset($item['lat'], $item['lon'])) {
                    return null;
                }

                return [
                    'lat' => (float) $item['lat'],
                    'lng' => (float) $item['lon'],
                ];
            } catch (\Throwable) {
                return null;
            }
        });
    }

    private function haversine(float $originLat, float $originLng, float $destinationLat, float $destinationLng): float
    {
        $earthRadius = 6371;
        $latDelta = deg2rad($destinationLat - $originLat);
        $lngDelta = deg2rad($destinationLng - $originLng);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($originLat)) * cos(deg2rad($destinationLat)) * sin($lngDelta / 2) ** 2;

        return round($earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);
    }

    private function normalizeCity(?string $city): string
    {
        $allowed = array_keys(self::FALLBACK_DISTANCES);

        return in_array($city, $allowed, true) ? $city : 'Toruń';
    }
}
