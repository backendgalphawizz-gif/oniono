<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesAdminGeoScope;
use App\Http\Controllers\Controller;
use App\Models\DeliveryPartner;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Services\CommissionOverrideService;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    use AppliesAdminGeoScope;

    public function __construct(private CommissionOverrideService $commissionOverrides) {}

    public function index()
    {
        $globalRestaurantPercentage = Setting::getValue('default_commission_percentage', 10);
        $globalDeliveryPercentage = Setting::getValue('default_delivery_partner_commission_percentage', 15);
        $commissionEnabled = (bool) Setting::getValue('commission_enabled', true);
        $defaultGstPercentage = Setting::getValue('default_gst_percentage', 5);

        return view('admin.commissions.index', compact(
            'globalRestaurantPercentage',
            'globalDeliveryPercentage',
            'commissionEnabled',
            'defaultGstPercentage',
        ));
    }

    public function updateGlobal(Request $request)
    {
        $data = $request->validate([
            'default_commission_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'default_delivery_partner_commission_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'default_gst_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'commission_enabled' => ['nullable', 'boolean'],
        ]);

        Setting::setValue('default_commission_percentage', $data['default_commission_percentage'], 'decimal', 'commission');
        Setting::setValue('default_delivery_partner_commission_percentage', $data['default_delivery_partner_commission_percentage'], 'decimal', 'commission');
        Setting::setValue('default_gst_percentage', $data['default_gst_percentage'], 'decimal', 'commission');
        Setting::setValue('commission_enabled', $request->boolean('commission_enabled'), 'boolean', 'commission');

        return back()->with('success', 'Master commission & GST settings saved.');
    }

    public function updateRestaurant(Request $request, Restaurant $restaurant)
    {
        $this->ensureRestaurantInScope($restaurant);
        $this->validateCommissionRequest($request);
        $this->commissionOverrides->applyRestaurantFromRequest($request, $restaurant);

        return back()->with('success', 'Restaurant commission updated.');
    }

    public function updateDeliveryPartner(Request $request, DeliveryPartner $deliveryPartner)
    {
        $this->validateCommissionRequest($request);
        $this->commissionOverrides->applyDeliveryPartnerFromRequest($request, $deliveryPartner);

        return back()->with('success', 'Delivery partner commission updated.');
    }

    protected function validateCommissionRequest(Request $request): void
    {
        if ($request->boolean('clear_override')) {
            return;
        }

        if ($request->has('commission_use_master') && $request->boolean('commission_use_master')) {
            return;
        }

        if ($request->has('commission_use_master') && ! $request->boolean('commission_use_master')) {
            $request->validate([
                'commission_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            ]);

            return;
        }

        if ($request->filled('percentage')) {
            $request->validate([
                'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            ]);
        }
    }
}
