<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\WebsiteEvent;
use App\Models\WebsiteVisit;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class WebsiteAnalyticsReport
{
    public function query(array $filters): Builder
    {
        return DB::table('website_events as e')->join('website_visits as v', 'v.id', '=', 'e.website_visit_id')
            ->whereBetween('e.business_date', [$filters['from'], $filters['to']])
            ->when($filters['source'] ?? null, fn ($q, $source) => $q->where('v.source', $source))
            ->when($filters['device'] ?? null, fn ($q, $device) => $q->where('v.device', $device));
    }

    private function metrics(Builder $query): object
    {
        return $query->selectRaw("COUNT(DISTINCT v.visitor_hash) as visitors, COUNT(DISTINCT v.id) as visits, COALESCE(SUM(CASE WHEN e.name = 'page_view' THEN 1 ELSE 0 END), 0) as views, COALESCE(SUM(CASE WHEN e.name = 'order_submitted' THEN 1 ELSE 0 END), 0) as orders")->first();
    }

    public function build(array $filters): array
    {
        $query = $this->query($filters);
        $from = CarbonImmutable::parse($filters['from'], 'Europe/Warsaw');
        $to = CarbonImmutable::parse($filters['to'], 'Europe/Warsaw');
        $days = (int) $from->diffInDays($to) + 1;
        $previous = [...$filters, 'from' => $from->subDays($days)->toDateString(), 'to' => $from->subDay()->toDateString()];
        $firstDate = WebsiteEvent::min('business_date');
        $daily = (clone $query)->selectRaw("e.business_date, COUNT(DISTINCT v.visitor_hash) as visitors, COUNT(DISTINCT v.id) as visits, SUM(CASE WHEN e.name = 'page_view' THEN 1 ELSE 0 END) as views, SUM(CASE WHEN e.name = 'order_submitted' THEN 1 ELSE 0 END) as orders")
            ->groupBy('e.business_date')->get()->keyBy('business_date');
        $series = [];
        foreach (CarbonPeriod::create($from, $to) as $day) {
            $date = $day->toDateString();
            $series[] = $daily->get($date) ?? (object) ['business_date' => $date, 'visitors' => 0, 'visits' => 0, 'views' => 0, 'orders' => 0];
        }
        $hours = (clone $query)->selectRaw('e.hour, COUNT(DISTINCT v.visitor_hash) as visitors')->groupBy('e.hour')->pluck('visitors', 'hour');
        $funnel = (clone $query)->selectRaw('e.name, COUNT(DISTINCT v.visitor_hash) as visitors')->groupBy('e.name')->pluck('visitors', 'name');
        $products = (clone $query)->whereNotNull('e.menu_item_id')->whereIn('e.name', ['product_view', 'add_to_cart'])
            ->selectRaw("e.menu_item_id, SUM(CASE WHEN e.name = 'product_view' THEN 1 ELSE 0 END) as views, SUM(CASE WHEN e.name = 'add_to_cart' THEN 1 ELSE 0 END) as additions")
            ->groupBy('e.menu_item_id')->orderByDesc('views')->orderByDesc('additions')->limit(12)->get();
        $names = MenuItem::whereIn('id', $products->pluck('menu_item_id'))->get()->keyBy('id');
        foreach ($products as $product) {
            $product->name = $names->get($product->menu_item_id)?->getTranslation('name', app()->getLocale()) ?? '#'.$product->menu_item_id;
        }

        return [
            'totals' => $this->metrics(clone $query),
            'previous' => $this->metrics($this->query($previous)),
            'hasComparison' => $firstDate && $firstDate <= $previous['from'],
            'firstDate' => $firstDate, 'daily' => collect($series), 'hours' => $hours, 'funnel' => $funnel,
            'products' => $products,
            'sources' => (clone $query)->selectRaw("v.source, COUNT(DISTINCT v.visitor_hash) as visitors, SUM(CASE WHEN e.name = 'order_submitted' THEN 1 ELSE 0 END) as orders")->groupBy('v.source')->orderByDesc('visitors')->limit(20)->get(),
            'devices' => (clone $query)->selectRaw('v.device, COUNT(DISTINCT v.visitor_hash) as visitors')->groupBy('v.device')->get(),
            'pages' => (clone $query)->where('e.name', 'page_view')->selectRaw('e.path, COUNT(*) as views, COUNT(DISTINCT v.visitor_hash) as visitors')->groupBy('e.path')->orderByDesc('views')->limit(12)->get(),
            'campaigns' => (clone $query)->whereNotNull('v.campaign')->selectRaw("v.campaign, COUNT(DISTINCT v.visitor_hash) as visitors, SUM(CASE WHEN e.name = 'order_submitted' THEN 1 ELSE 0 END) as orders")->groupBy('v.campaign')->orderByDesc('visitors')->limit(12)->get(),
        ];
    }

    public function sources(): array
    {
        return WebsiteVisit::distinct()->orderBy('source')->pluck('source')->mapWithKeys(fn ($source) => [$source => self::sourceLabel($source)])->all();
    }

    public static function sourceLabel(string $source): string
    {
        return match ($source) {
            'direct' => __('traffic.direct'), 'google' => 'Google', 'bing' => 'Bing', 'instagram' => 'Instagram', 'facebook' => 'Facebook',
            default => preg_replace('/^(utm:|ref:)/', '', $source),
        };
    }
}
