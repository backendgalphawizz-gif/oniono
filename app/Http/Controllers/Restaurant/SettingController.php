<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingController extends Controller
{
    public function edit()
    {
        return view('restaurant.settings.edit');
    }

    public function updatePassword(Request $request)
    {
        $user = auth('restaurant')->user();

        $request->validate([
            'current_password' => ['required', 'current_password:restaurant'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated.');
    }
}
