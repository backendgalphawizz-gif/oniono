<?php

namespace App\Support;

use App\Models\City;
use App\Models\State;
use Illuminate\Validation\ValidationException;

class LocationNames
{
    /**
     * @param  array{state_id: int|string, city_id: int|string}  $data
     * @return array{state_id: int, city_id: int, state: string, city: string}
     */
    public static function resolveFromIds(array $data): array
    {
        $stateId = (int) $data['state_id'];
        $cityId = (int) $data['city_id'];

        $state = State::query()->find($stateId);
        $city = City::query()
            ->where('id', $cityId)
            ->where('state_id', $stateId)
            ->first();

        if (! $state || ! $city) {
            throw ValidationException::withMessages([
                'city_id' => 'Select a valid city for the chosen state.',
            ]);
        }

        return [
            'state_id' => $stateId,
            'city_id' => $cityId,
            'state' => $state->name,
            'city' => $city->name,
        ];
    }
}
