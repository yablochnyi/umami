<?php

namespace App\Http\Controllers;

use App\Services\GoOrder\MenuAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class MenuAvailabilityController extends Controller
{
    public function __invoke(Request $request, MenuAvailability $availability)
    {
        $data = $request->validate([
            'locale' => ['nullable', 'in:pl,uk,en'],
            'day' => ['nullable', 'required_with:time', 'date_format:Y-m-d'],
            'time' => ['nullable', 'required_with:day', 'date_format:H:i'],
            'type' => ['nullable', 'in:PICK_UP,DELIVERY'],
        ]);
        app()->setLocale($data['locale'] ?? 'pl');
        $at = isset($data['day'], $data['time']) ? CarbonImmutable::createFromFormat('!Y-m-d H:i', $data['day'].' '.$data['time'], 'Europe/Warsaw') : null;

        return response()->json($availability->snapshot($at, $data['type'] ?? null))->header('Cache-Control', 'no-store');
    }
}
