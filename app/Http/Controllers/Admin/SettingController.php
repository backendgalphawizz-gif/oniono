<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function general()
    {
        return view('admin.settings.general');
    }

    public function site(SiteSettingsService $siteSettings)
    {
        return view('admin.settings.site', [
            'site' => $siteSettings->allForForm(),
            'timezones' => timezone_identifiers_list(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'cod_enabled' => ['nullable', 'boolean'],
            'online_payment_enabled' => ['nullable', 'boolean'],
            'delivery_partner_reviews_enabled' => ['nullable', 'boolean'],
            'admin_food_price_editing' => ['nullable', 'boolean'],
        ]);

        Setting::setValue('cod_enabled', $request->boolean('cod_enabled'), 'boolean', 'payment');
        Setting::setValue('online_payment_enabled', $request->boolean('online_payment_enabled'), 'boolean', 'payment');
        Setting::setValue('delivery_partner_reviews_enabled', $request->boolean('delivery_partner_reviews_enabled'), 'boolean', 'delivery');
        Setting::setValue('admin_food_price_editing', $request->boolean('admin_food_price_editing'), 'boolean', 'catalog');

        return back()->with('success', 'Settings saved.');
    }

    public function updateSite(Request $request, SiteSettingsService $siteSettings)
    {
        $siteSettings->updateFromRequest($request);

        return redirect()
            ->route('admin.settings.site')
            ->with('success', 'Site settings saved.');
    }
}
