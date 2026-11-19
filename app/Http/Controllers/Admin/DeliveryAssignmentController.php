<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Admin\Concerns\AppliesAdminGeoScope;
use App\Http\Controllers\Controller;
use App\Models\DeliveryPartner;
use App\Models\Order;
use App\Services\DeliveryAssignmentService;
use Illuminate\Http\Request;

class DeliveryAssignmentController extends Controller
{
    use AppliesAdminGeoScope;

    public function __construct(private DeliveryAssignmentService $assignmentService) {}

    public function index()
    {
        $orders = $this->scopeOrders(Order::query())
            ->with(['restaurant', 'deliveryAssignment.deliveryPartner'])
            ->where('status', OrderStatus::ReadyForPickup)
            ->latest()
            ->paginate(admin_listing_per_page())
            ->withQueryString();

        $partners = DeliveryPartner::query()
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where('is_blocked', false)
            ->where('is_suspended', false)
            ->orderBy('name')
            ->get();

        return view('admin.delivery-assignment.index', compact('orders', 'partners'));
    }

    public function assign(Request $request, Order $order)
    {
        $this->ensureOrderInScope($order);
        $data = $request->validate([
            'delivery_partner_id' => ['required', 'exists:delivery_partners,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $partner = DeliveryPartner::query()->findOrFail($data['delivery_partner_id']);

        $this->assignmentService->assign($order, $partner, auth('admin')->id(), $data['note'] ?? null);

        return back()->with('success', 'Delivery partner assigned.');
    }
}
