<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminGeoScope;
use App\Models\City;
use App\Models\Restaurant;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $states = State::query()
            ->withCount('cities')
            ->when($request->filled('state_search'), function ($q) use ($request) {
                $s = $request->string('state_search')->trim();
                $q->where('name', 'like', "%{$s}%");
            })
            ->orderBy('name')
            ->paginate(admin_listing_per_page(), ['*'], 'states_page')
            ->withQueryString();

        $selectedStateId = $request->integer('state_id');
        $selectedState = $selectedStateId
            ? State::query()->find($selectedStateId)
            : State::query()->orderBy('name')->first();

        $cities = null;
        if ($selectedState) {
            $cities = City::query()
                ->where('state_id', $selectedState->id)
                ->when($request->filled('city_search'), function ($q) use ($request) {
                    $s = $request->string('city_search')->trim();
                    $q->where('name', 'like', "%{$s}%");
                })
                ->orderBy('name')
                ->paginate(admin_listing_per_page(), ['*'], 'cities_page')
                ->withQueryString();
        }

        $allStates = State::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.settings.locations.index', compact('states', 'selectedState', 'cities', 'allStates'));
    }

    public function createState()
    {
        return view('admin.settings.locations.state-form', [
            'state' => null,
            'action' => route('admin.settings.locations.states.store'),
        ]);
    }

    public function storeState(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:states,name'],
        ]);

        State::query()->create(['name' => trim($data['name'])]);

        return redirect()
            ->route('admin.settings.locations.index')
            ->with('success', 'State added.');
    }

    public function editState(State $state)
    {
        return view('admin.settings.locations.state-form', [
            'state' => $state,
            'action' => route('admin.settings.locations.states.update', $state),
            'method' => 'PUT',
        ]);
    }

    public function updateState(Request $request, State $state)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('states', 'name')->ignore($state->id),
            ],
        ]);

        $newName = trim($data['name']);
        $oldName = $state->name;

        $state->update(['name' => $newName]);

        if ($oldName !== $newName) {
            $this->syncLocationNameChanges($oldName, $newName, null, null);
        }

        return redirect()
            ->route('admin.settings.locations.index', ['state_id' => $state->id])
            ->with('success', 'State updated.');
    }

    public function destroyState(State $state)
    {
        $stateName = $state->name;

        if (Restaurant::query()->where('state', $stateName)->exists()) {
            return back()->withErrors([
                'state' => 'Cannot delete this state while restaurants still use it. Rename or reassign those restaurants first.',
            ]);
        }

        $state->delete();

        return redirect()
            ->route('admin.settings.locations.index')
            ->with('success', 'State and its cities removed.');
    }

    public function createCity(Request $request)
    {
        $stateId = $request->integer('state_id');
        $state = $stateId ? State::query()->findOrFail($stateId) : null;
        $states = State::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.settings.locations.city-form', [
            'city' => null,
            'state' => $state,
            'states' => $states,
            'action' => route('admin.settings.locations.cities.store'),
        ]);
    }

    public function storeCity(Request $request)
    {
        $data = $request->validate([
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('cities', 'name')->where('state_id', $request->integer('state_id')),
            ],
        ]);

        $city = City::query()->create([
            'state_id' => $data['state_id'],
            'name' => trim($data['name']),
        ]);

        return redirect()
            ->route('admin.settings.locations.index', ['state_id' => $city->state_id])
            ->with('success', 'City added.');
    }

    public function editCity(City $city)
    {
        $city->load('state');
        $states = State::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.settings.locations.city-form', [
            'city' => $city,
            'state' => $city->state,
            'states' => $states,
            'action' => route('admin.settings.locations.cities.update', $city),
            'method' => 'PUT',
        ]);
    }

    public function updateCity(Request $request, City $city)
    {
        $data = $request->validate([
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('cities', 'name')
                    ->where('state_id', $request->integer('state_id'))
                    ->ignore($city->id),
            ],
        ]);

        $city->load('state');
        $oldStateName = $city->state->name;
        $oldCityName = $city->name;
        $newCityName = trim($data['name']);
        $newState = State::query()->findOrFail($data['state_id']);

        $city->update([
            'state_id' => $data['state_id'],
            'name' => $newCityName,
        ]);

        if ($oldCityName !== $newCityName || $oldStateName !== $newState->name) {
            $this->syncLocationNameChanges($oldStateName, $newState->name, $oldCityName, $newCityName);
        }

        return redirect()
            ->route('admin.settings.locations.index', ['state_id' => $city->state_id])
            ->with('success', 'City updated.');
    }

    public function destroyCity(City $city)
    {
        $city->load('state');
        $stateName = $city->state->name;
        $cityName = $city->name;
        $stateId = $city->state_id;

        if (Restaurant::query()->where('state', $stateName)->where('city', $cityName)->exists()) {
            return back()->withErrors([
                'city' => 'Cannot delete this city while restaurants still use it.',
            ]);
        }

        $city->delete();

        return redirect()
            ->route('admin.settings.locations.index', ['state_id' => $stateId])
            ->with('success', 'City removed.');
    }

    private function syncLocationNameChanges(
        string $oldStateName,
        string $newStateName,
        ?string $oldCityName = null,
        ?string $newCityName = null,
    ): void {
        if ($oldCityName !== null && $newCityName !== null) {
            if ($oldCityName !== $newCityName || $oldStateName !== $newStateName) {
                Restaurant::query()
                    ->where('state', $oldStateName)
                    ->where('city', $oldCityName)
                    ->update([
                        'state' => $newStateName,
                        'city' => $newCityName,
                    ]);
                AdminGeoScope::query()
                    ->where('state', $oldStateName)
                    ->where('city', $oldCityName)
                    ->update([
                        'state' => $newStateName,
                        'city' => $newCityName,
                    ]);
            }

            return;
        }

        if ($oldStateName !== $newStateName) {
            Restaurant::query()
                ->where('state', $oldStateName)
                ->update(['state' => $newStateName]);
            AdminGeoScope::query()
                ->where('state', $oldStateName)
                ->update(['state' => $newStateName]);
        }
    }
}
