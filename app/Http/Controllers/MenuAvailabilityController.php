<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuAvailabilityRequest;
use App\Services\GoOrder\MenuAvailability;
use Carbon\CarbonImmutable;

class MenuAvailabilityController extends Controller
{
    public function __invoke(MenuAvailabilityRequest $request, MenuAvailability $availability)
    {
        $data = $request->validated();
        app()->setLocale($data['locale'] ?? 'pl');
        $at = isset($data['day'], $data['time']) ? CarbonImmutable::createFromFormat('!Y-m-d H:i', $data['day'].' '.$data['time'], 'Europe/Warsaw') : null;

        return response()->json($availability->snapshot($at, $data['type'] ?? null))->header('Cache-Control', 'no-store');
    }
}
