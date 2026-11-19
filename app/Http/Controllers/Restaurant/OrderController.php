<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request)
    {
        $restaurantId = restaurant_id();
        $baseQuery = fn (): Builder => Order::query()->where('restaurant_id', $restaurantId);

        $counts = [
            'all' => (clone $baseQuery())->count(),
            'pending' => (clone $baseQuery())->where('status', OrderStatus::Pending)->count(),
            'accepted' => (clone $baseQuery())->where('status', OrderStatus::Accepted)->count(),
            'preparing' => (clone $baseQuery())->where('status', OrderStatus::Preparing)->count(),
            'ready_for_pickup' => (clone $baseQuery())->where('status', OrderStatus::ReadyForPickup)->count(),
            'in_delivery' => (clone $baseQuery())->whereIn('status', [OrderStatus::PickedUp, OrderStatus::OnTheWay])->count(),
            'delivered' => (clone $baseQuery())->where('status', OrderStatus::Delivered)->count(),
            'cancelled' => (clone $baseQuery())->whereIn('status', OrderStatus::restaurantTerminalNegativeValues())->count(),
        ];

        $orders = $baseQuery()
            ->with(['deliveryAssignment.deliveryPartner'])
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'cancelled') {
                    $query->whereIn('status', OrderStatus::restaurantTerminalNegativeValues());
                } elseif ($request->status === 'in_delivery') {
                    $query->whereIn('status', [OrderStatus::PickedUp->value, OrderStatus::OnTheWay->value]);
                } else {
                    $query->where('status', $request->status);
                }
            })
            ->when(! $request->filled('status') && $request->filled('bucket'), function ($query) use ($request) {
                match ($request->string('bucket')->toString()) {
                    'new' => $query->where('status', OrderStatus::Pending),
                    'ongoing' => $query->whereIn('status', OrderStatus::restaurantOngoingValues()),
                    'completed' => $query->where('status', OrderStatus::Delivered),
                    'cancelled' => $query->whereIn('status', OrderStatus::restaurantTerminalNegativeValues()),
                    default => null,
                };
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $filters = [
            ['param' => null, 'label' => 'All', 'count' => $counts['all']],
            ['param' => 'pending', 'label' => 'New', 'count' => $counts['pending']],
            ['param' => 'accepted', 'label' => 'Accepted', 'count' => $counts['accepted']],
            ['param' => 'preparing', 'label' => 'Preparing', 'count' => $counts['preparing']],
            ['param' => 'ready_for_pickup', 'label' => 'Ready for pickup', 'count' => $counts['ready_for_pickup']],
            ['param' => 'in_delivery', 'label' => 'Out for delivery', 'count' => $counts['in_delivery']],
            ['param' => 'delivered', 'label' => 'Completed', 'count' => $counts['delivered']],
            ['param' => 'cancelled', 'label' => 'Cancelled', 'count' => $counts['cancelled']],
        ];

        return view('restaurant.orders.index', compact('orders', 'counts', 'filters'));
    }

    public function show(Order $order)
    {
        abort_if($order->restaurant_id !== restaurant_id(), 403);

        $order->load([
            'items',
            'statusHistories' => fn ($q) => $q->latest(),
            'payment',
            'address',
            'user',
            'deliveryAssignment.deliveryPartner',
            'deliveryAssignments.deliveryPartner',
        ]);

        $allowedStatusUpdates = $this->orderService->allowedRestaurantStatusUpdates($order);

        return view('restaurant.orders.show', compact('order', 'allowedStatusUpdates'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        abort_if($order->restaurant_id !== restaurant_id(), 403);

        $toStatus = OrderStatus::from($request->input('status'));

        $rules = [
            'status' => ['required', 'string'],
            'note' => ['nullable', 'string', 'max:500'],
        ];

        if ($toStatus === OrderStatus::Rejected) {
            $rules['note'] = ['required', 'string', 'max:500'];
        }

        $request->validate($rules);

        $this->orderService->transition(
            $order,
            $toStatus,
            'restaurant_user',
            auth('restaurant')->id(),
            $request->note,
            'restaurant'
        );

        return back()->with('success', 'Order updated to '.$toStatus->label().'.');
    }
}
