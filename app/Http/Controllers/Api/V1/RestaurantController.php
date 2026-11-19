<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RestaurantStatus;
use App\Http\Controllers\Controller;
use App\Models\FoodItem;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $restaurants = Restaurant::query()
            ->where('status', RestaurantStatus::Approved)
            ->where('is_open', true)
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->paginate(20);

        return response()->json($restaurants);
    }

    public function show(Restaurant $restaurant)
    {
        abort_if($restaurant->status !== RestaurantStatus::Approved, 404);

        return response()->json($restaurant->load('cuisines'));
    }

    public function menu(Restaurant $restaurant)
    {
        abort_if($restaurant->status !== RestaurantStatus::Approved, 404);

        $items = FoodItem::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('is_available', true)
            ->with(['variants', 'addons', 'category'])
            ->get();

        return response()->json($items);
    }
}
