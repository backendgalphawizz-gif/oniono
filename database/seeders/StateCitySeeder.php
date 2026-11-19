<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\State;
use Illuminate\Database\Seeder;

class StateCitySeeder extends Seeder
{
    public function run(): void
    {
        $cityMap = require database_path('data/india_state_cities.php');
        $configStates = config('india_states.states', []);

        foreach ($configStates as $stateName) {
            $stateName = trim((string) $stateName);
            if ($stateName === '') {
                continue;
            }

            $state = State::query()->updateOrCreate(['name' => $stateName]);

            $cities = $cityMap[$stateName] ?? [$stateName];
            $cities = array_values(array_unique(array_filter(array_map(
                fn ($c) => trim((string) $c),
                $cities
            ))));

            foreach ($cities as $cityName) {
                City::query()->updateOrCreate(
                    ['state_id' => $state->id, 'name' => $cityName]
                );
            }
        }

        $this->mergeExistingRestaurantLocations();
    }

    protected function mergeExistingRestaurantLocations(): void
    {
        $rows = \App\Models\Restaurant::query()
            ->select(['state', 'city'])
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->get();

        foreach ($rows as $row) {
            $state = State::query()->firstOrCreate(['name' => trim($row->state)]);
            City::query()->firstOrCreate([
                'state_id' => $state->id,
                'name' => trim($row->city),
            ]);
        }
    }
}
