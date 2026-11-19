<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Services\RestaurantDocumentService;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $restaurant = auth('restaurant')->user()->restaurant;
        $restaurant->load(['documents', 'images']);
        $timings = $restaurant->timings()->orderBy('day_of_week')->get()->keyBy('day_of_week');
        $holidays = $restaurant->holidays()->orderBy('date')->get();
        $documentChecklist = app(RestaurantDocumentService::class)->checklist($restaurant);

        return view('restaurant.profile.edit', compact('restaurant', 'timings', 'holidays', 'documentChecklist'));
    }

    public function update(Request $request)
    {
        $restaurant = auth('restaurant')->user()->restaurant;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('restaurants', 'phone')->ignore($restaurant->id),
            ],
            'description' => ['nullable', 'string'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:10'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_ifsc' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ] + app(RestaurantDocumentService::class)->optionalUpdateValidationRules());

        $data['phone'] = PhoneNumber::normalize($data['phone']);
        if ($data['phone'] === '') {
            return back()->withErrors(['phone' => 'Enter a valid mobile number.'])->withInput();
        }

        if ($request->boolean('remove_logo') && $restaurant->logo) {
            Storage::disk('public')->delete($restaurant->logo);
            $data['logo'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($restaurant->logo) {
                Storage::disk('public')->delete($restaurant->logo);
            }
            $data['logo'] = $request->file('logo')->store('restaurants/logos', 'public');
        }

        unset($data['remove_logo']);

        $restaurant->update(collect($data)->only([
            'name', 'owner_name', 'phone', 'description', 'address', 'city', 'state', 'pincode',
            'bank_account_name', 'bank_account_number', 'bank_ifsc', 'bank_name', 'logo',
        ])->all());

        app(RestaurantDocumentService::class)->storeFromRequest($request, $restaurant);

        return redirect()
            ->route('restaurant.profile.edit')
            ->with('success', 'Profile updated.')
            ->withFragment('profile-details');
    }
}
