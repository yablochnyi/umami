<?php

namespace App\Services;

use App\Models\Order;
use App\Models\WebsiteEvent;
use App\Models\WebsiteVisit;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Throwable;

class WebsiteAnalytics
{
    private const PUBLIC_ROUTES = ['home', 'home.localized', 'menu.category', 'menu.category.localized', 'menu.item', 'menu.item.localized', 'checkout', 'checkout.localized', 'legal', 'legal.localized'];

    public function record(Request $request, array $data): void
    {
        if (! $this->eligible($request)) {
            return;
        }
        try {
            $route = Route::getRoutes()->match(Request::create($data['path']))->getName();
            if (! in_array($route, self::PUBLIC_ROUTES, true)
                || ($data['name'] === 'begin_checkout' && ! str_starts_with($route, 'checkout'))) {
                return;
            }
            $this->store($request, $data);
        } catch (Throwable $exception) {
            // Analytics failures must never interrupt browsing or checkout; do not log payloads.
            Log::warning('Website analytics event was not stored.', ['exception' => $exception::class]);
        }
    }

    public function orderSubmitted(Request $request, Order $order): void
    {
        if (! $this->eligible($request) || ! in_array($order->status, ['sent_to_gopos', 'waiting_staff', 'accepted', 'ready', 'delivering', 'completed'], true)) {
            return;
        }
        try {
            $this->store($request, ['name' => 'order_submitted', 'path' => '/koszyk', 'event_id' => 'order:'.$order->id], true);
        } catch (Throwable $exception) {
            Log::warning('Website analytics conversion was not stored.', ['exception' => $exception::class]);
        }
    }

    private function eligible(Request $request): bool
    {
        return config('website_analytics.enabled')
            && ! $request->user()
            && $request->cookie('umami_cookie_consent') === 'accepted'
            && Str::isUuid($request->cookie('umami_visitor_id', ''))
            && ! preg_match('/bot|crawler|spider|headless|lighthouse|pagespeed|preview|facebookexternalhit|monitor/i', $request->userAgent() ?? '');
    }

    private function store(Request $request, array $data, bool $conversion = false): void
    {
        $hash = hash_hmac('sha256', $request->cookie('umami_visitor_id'), config('app.key'));
        $eventKey = hash_hmac('sha256', ($conversion ? '' : $hash.'|').$data['event_id'], config('app.key'));
        Cache::lock('website-analytics:'.$hash, 10)->block(2, function () use ($request, $data, $hash, $eventKey, $conversion): void {
            DB::transaction(function () use ($request, $data, $hash, $eventKey, $conversion): void {
                if (WebsiteEvent::where('event_key', $eventKey)->exists()) {
                    return;
                }
                $now = CarbonImmutable::now('UTC');
                $visit = WebsiteVisit::where('visitor_hash', $hash)
                    ->where('last_seen_at', '>', $now->subMinutes(config('website_analytics.session_minutes')))
                    ->latest('last_seen_at')->first();
                // Only attribute orders to an observed, consented visit.
                if (! $visit && $conversion) {
                    return;
                }
                if (! $visit) {
                    $ua = $request->userAgent() ?? '';
                    $visit = WebsiteVisit::create([
                        'visitor_hash' => $hash, 'started_at' => $now, 'last_seen_at' => $now,
                        'source' => $this->source($request, $data), 'campaign' => $data['utm_campaign'] ?? null,
                        'device' => preg_match('/ipad|tablet/i', $ua) ? 'tablet' : (preg_match('/mobile|iphone|android/i', $ua) ? 'mobile' : 'desktop'),
                    ]);
                } else {
                    $visit->update(['last_seen_at' => $now]);
                }
                $local = $now->setTimezone('Europe/Warsaw');
                WebsiteEvent::create([
                    'event_key' => $eventKey, 'website_visit_id' => $visit->id,
                    'occurred_at' => $now, 'business_date' => $local->toDateString(), 'hour' => $local->hour,
                    'name' => $data['name'], 'path' => $data['path'], 'menu_item_id' => $data['menu_item_id'] ?? null,
                ]);
            });
        });
    }

    private function source(Request $request, array $data): string
    {
        if (! empty($data['utm_source'])) {
            return 'utm:'.substr(strtolower($data['utm_source']), 0, 96);
        }
        $host = strtolower($data['referrer_host'] ?? '');
        if (! $host || in_array($host, [$request->getHost(), 'umamisushifood.pl', 'www.umamisushifood.pl'], true)) {
            return 'direct';
        }
        foreach (['google' => '/(^|\.)google\.[a-z.]+$/', 'instagram' => '/(^|\.)instagram\.com$/', 'facebook' => '/(^|\.)(facebook\.com|fb\.com)$/', 'bing' => '/(^|\.)bing\.com$/'] as $source => $pattern) {
            if (preg_match($pattern, $host)) {
                return $source;
            }
        }

        return 'ref:'.substr($host, 0, 96);
    }
}
