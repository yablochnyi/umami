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
        $destination = $this->destination($city, $street, $building);
        $distance = $this->distance($settings, $city, $destination);
        $zone = $this->streetZone($settings, $street) ?? $this->zoneForDistance($settings, $distance);

        return [
            'city' => $city,
            'distance_km' => $distance,
            'cost' => $this->costForZone($settings, $zone, $distance),
            'zone' => $zone,
            'coordinates' => $destination,
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

    private function costForZone(array $settings, array $zone, ?float $distance): float
    {
        return match ((string) ($zone['tier'] ?? '')) {
            '1' => (float) ($settings['deliveryTier1Cost'] ?? 9.99),
            '2' => (float) ($settings['deliveryTier2Cost'] ?? 14.99),
            '3' => (float) ($settings['deliveryTier3Cost'] ?? 24.99),
            default => $this->costForDistance($settings, $distance),
        };
    }

    private function streetZone(array $settings, ?string $street): ?array
    {
        $street = $this->normalizeStreet($street);
        if ($street === '') {
            return null;
        }

        foreach ([1, 2, 3] as $tier) {
            $streets = $this->streetList((string) ($settings["deliveryTier{$tier}Streets"] ?? ''));
            if (in_array($street, $streets, true)) {
                return [
                    'tier' => (string) $tier,
                    'id' => (string) ($settings["deliveryTier{$tier}ZoneId"] ?? ($tier + 1)),
                    'name' => (string) ($settings["deliveryTier{$tier}ZoneName"] ?? "Strefa {$tier}"),
                ];
            }
        }

        return null;
    }

    private function streetList(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n|,/', $value) ?: [])
            ->map(fn (string $street): string => $this->normalizeStreet($street))
            ->filter()
            ->values()
            ->all();
    }

    private function normalizeStreet(?string $street): string
    {
        return str($street ?? '')
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish()
            ->toString();
    }

    private function zoneForDistance(array $settings, ?float $distance): array
    {
        $distance ??= (float) ($settings['deliveryTier1MaxKm'] ?? 3);

        if ($distance <= (float) ($settings['deliveryTier1MaxKm'] ?? 3)) {
            return [
                'tier' => '1',
                'id' => (string) ($settings['deliveryTier1ZoneId'] ?? '2'),
                'name' => (string) ($settings['deliveryTier1ZoneName'] ?? 'Strefa 1'),
            ];
        }

        if ($distance <= (float) ($settings['deliveryTier2MaxKm'] ?? 8)) {
            return [
                'tier' => '2',
                'id' => (string) ($settings['deliveryTier2ZoneId'] ?? '3'),
                'name' => (string) ($settings['deliveryTier2ZoneName'] ?? 'Strefa 2'),
            ];
        }

        return [
            'tier' => '3',
            'id' => (string) ($settings['deliveryTier3ZoneId'] ?? '4'),
            'name' => (string) ($settings['deliveryTier3ZoneName'] ?? 'Strefa 3'),
        ];
    }

    private function destination(string $city, ?string $street, ?string $building): ?array
    {
        if (! filled($street)) {
            return null;
        }

        return $this->geocode($city, $street, $building);
    }

    private function distance(array $settings, string $city, ?array $destination): ?float
    {
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
