<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Admin\Concerns\AppliesAdminGeoScope;
use App\Http\Controllers\Controller;
use App\Models\DeliveryPartner;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use AppliesAdminGeoScope;

    public function __construct(private OrderService $orderService) {}

    public function index(Request $request)
    {
        $orders = $this->scopeOrders(Order::query())
            ->with(['restaurant', 'user', 'deliveryAssignment.deliveryPartner'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->filled('restaurant_id'), fn ($q) => $q->where('restaurant_id', $request->restaurant_id))
            ->when($request->filled('search'), fn ($q) => $q->where('order_number', 'like', '%'.$request->string('search')->trim().'%'))
            ->latest()
            ->paginate(admin_listing_per_page())
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->ensureOrderInScope($order);
        $order->load([
            'items.foodItem',
            'restaurant',
            'user',
            'address',
            'statusHistories' => fn ($q) => $q->orderBy('created_at'),
            'payment',
            'deliveryAssignments.deliveryPartner',
            'deliveryAssignment.deliveryPartner',
        ]);

        $canAdminCancel = $this->orderService->canAdminCancel($order);
        $allowedStatusUpdates = $this->orderService->allowedAdminStatusUpdates($order);
        $canReassignDelivery = $this->orderService->canAdminReassignDeliveryPartner($order);

        $deliveryPartners = $canReassignDelivery
            ? DeliveryPartner::query()
                ->where('status', 'approved')
                ->where('is_active', true)
                ->where('is_blocked', false)
                ->where('is_suspended', false)
                ->orderBy('name')
                ->get()
            : collect();

        return view('admin.orders.show', compact(
            'order',
            'canAdminCancel',
            'allowedStatusUpdates',
            'canReassignDelivery',
            'deliveryPartners',
        ));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->ensureOrderInScope($order);
        $request->validate(['status' => ['required', 'string']]);
        $status = OrderStatus::from($request->status);

        $this->orderService->transition(
            $order,
            $status,
            'admin',
            auth('admin')->id(),
            $request->note,
            'admin'
        );

        return back()->with('success', 'Order status updated.');
    }

    public function cancel(Request $request, Order $order)
    {
        $this->ensureOrderInScope($order);
        if (! $this->orderService->canAdminCancel($order)) {
            return back()->withErrors(['cancellation_reason' => 'This order cannot be cancelled at its current status.']);
        }

        $request->validate(['cancellation_reason' => ['required', 'string', 'max:500']]);

        $this->orderService->transition(
            $order,
            OrderStatus::Cancelled,
            'admin',
            auth('admin')->id(),
            $request->cancellation_reason,
            'admin'
        );

        $order->update(['cancellation_reason' => $request->cancellation_reason]);

        return back()->with('success', 'Order cancelled.');
    }
}
