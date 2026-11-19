<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesAdminGeoScope;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Settlement;
use App\Services\SettlementService;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    use AppliesAdminGeoScope;

    public function __construct(private SettlementService $settlementService) {}

    public function index()
    {
        $settlements = $this->scopeSettlements(Settlement::query())->with('restaurant')->latest()->paginate(admin_listing_per_page())->withQueryString();
        $restaurants = $this->scopeRestaurants(Restaurant::query())->orderBy('name')->get();

        return view('admin.settlements.index', compact('settlements', 'restaurants'));
    }

    public function create(Restaurant $restaurant)
    {
        $this->ensureRestaurantInScope($restaurant);
        $earnings = $this->settlementService->pendingEarnings($restaurant);

        return view('admin.settlements.create', compact('restaurant', 'earnings'));
    }

    public function store(Request $request, Restaurant $restaurant)
    {
        $this->ensureRestaurantInScope($restaurant);
        $data = $request->validate([
            'earning_ids' => ['required', 'array', 'min:1'],
            'earning_ids.*' => ['integer', 'exists:restaurant_earnings,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_mode' => ['nullable', 'string', 'max:100'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'payment_date' => ['nullable', 'date'],
            'admin_remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $settlement = $this->settlementService->createSettlement(
            $restaurant,
            $data['earning_ids'],
            (float) $data['amount'],
            auth('admin')->id(),
            $data
        );

        return redirect()->route('admin.settlements.show', $settlement)->with('success', 'Settlement recorded.');
    }

    public function show(Settlement $settlement)
    {
        $this->ensureSettlementInScope($settlement);
        $settlement->load(['restaurant', 'items.earning.order', 'admin']);

        return view('admin.settlements.show', compact('settlement'));
    }
}
