<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function cities(Request $request): JsonResponse
    {
        $data = $request->validate([
            'state_id' => ['required', 'integer', 'exists:states,id'],
        ]);

        $cities = City::query()
            ->where('state_id', $data['state_id'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $cities]);
    }
}
