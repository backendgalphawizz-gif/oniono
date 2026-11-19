<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login_id' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::findForLogin($data['login_id']);

        if (! $admin || ! Hash::check($data['password'], $admin->password)) {
            return back()
                ->withErrors(['login_id' => 'Invalid admin ID, email, or password.'])
                ->onlyInput('login_id');
        }

        if (! $admin->is_active) {
            return back()
                ->withErrors(['login_id' => 'This admin account is inactive.'])
                ->onlyInput('login_id');
        }

        if ($admin->isEmployee()) {
            $admin->load('permissions');
        }

        Auth::guard('admin')->login($admin, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(admin_home_url());
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
