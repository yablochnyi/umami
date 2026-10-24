<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWebsiteEventRequest;
use App\Services\WebsiteAnalytics;
use Symfony\Component\HttpFoundation\Response;

class WebsiteEventController extends Controller
{
    public function __invoke(StoreWebsiteEventRequest $request, WebsiteAnalytics $analytics): Response
    {
        $analytics->record($request, $request->validated());

        return response()->noContent()->header('Cache-Control', 'no-store');
    }
}
