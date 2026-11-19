<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\RestaurantHoliday;
use App\Models\RestaurantTiming;
use Illuminate\Http\Request;

class TimingController extends Controller
{
    public function edit()
    {
        return redirect()->route('restaurant.profile.edit')->withFragment('opening-hours');
    }

    public function update(Request $request)
    {
        $restaurant = auth('restaurant')->user()->restaurant;

        $data = $request->validate([
            'is_open' => ['nullable', 'boolean'],
            'timings' => ['nullable', 'array'],
            'timings.*.opens_at' => ['nullable', 'date_format:H:i'],
            'timings.*.closes_at' => ['nullable', 'date_format:H:i'],
            'timings.*.is_closed' => ['nullable', 'boolean'],
            'holiday_date' => ['nullable', 'date', 'after_or_equal:today'],
            'holiday_reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->has('from_availability') || $request->has('is_open')) {
            $restaurant->update(['is_open' => $request->boolean('is_open')]);
        }

        foreach ($data['timings'] ?? [] as $day => $timing) {
            RestaurantTiming::query()->updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'day_of_week' => (int) $day],
                [
                    'opens_at' => empty($timing['is_closed']) ? ($timing['opens_at'] ?? null) : null,
                    'closes_at' => empty($timing['is_closed']) ? ($timing['closes_at'] ?? null) : null,
                    'is_closed' => ! empty($timing['is_closed']),
                ]
            );
        }

        if ($request->filled('holiday_date')) {
            RestaurantHoliday::query()->firstOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'date' => $data['holiday_date'],
                ],
                ['reason' => $data['holiday_reason'] ?? null]
            );
        }

        $fromAvailability = $request->boolean('from_availability');

        return redirect()
            ->route('restaurant.profile.edit')
            ->with('success', $fromAvailability
                ? 'Restaurant availability updated.'
                : 'Opening hours updated.')
            ->withFragment($fromAvailability ? 'availability' : 'opening-hours');
    }

    public function destroyHoliday(RestaurantHoliday $holiday)
    {
        abort_if($holiday->restaurant_id !== restaurant_id(), 403);
        $holiday->delete();

        return redirect()
            ->route('restaurant.profile.edit')
            ->with('success', 'Holiday removed.')
            ->withFragment('opening-hours');
    }
}
