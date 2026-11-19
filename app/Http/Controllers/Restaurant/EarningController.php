<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\RestaurantEarning;
use App\Models\Settlement;
use App\Services\RestaurantFinanceService;
use Illuminate\Http\Request;

class EarningController extends Controller
{
    public function index(Request $request)
    {
        $restaurantId = restaurant_id();
        $finance = RestaurantFinanceService::forAuthenticatedRestaurant();

        $earnings = RestaurantEarning::query()
            ->where('restaurant_id', $restaurantId)
            ->with('order')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $overview = $finance->overview();
        $dailyBreakdown = $finance->dailyBreakdown(14);

        $recentSettlements = Settlement::query()
            ->where('restaurant_id', $restaurantId)
            ->latest()
            ->limit(5)
            ->get();

        return view('restaurant.earnings.index', compact(
            'earnings',
            'overview',
            'dailyBreakdown',
            'recentSettlements',
        ));
    }
}
