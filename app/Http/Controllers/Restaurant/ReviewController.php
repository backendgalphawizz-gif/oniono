<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $restaurantId = restaurant_id();

        $visibleBase = Review::query()
            ->where('restaurant_id', $restaurantId)
            ->where('is_visible', true);

        $summary = [
            'total' => (clone $visibleBase)->count(),
            'average' => round((float) (clone $visibleBase)->avg('rating'), 1),
        ];

        $distribution = (clone $visibleBase)
            ->select('rating', DB::raw('COUNT(*) as total'))
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $starCounts = [];
        for ($star = 5; $star >= 1; $star--) {
            $starCounts[$star] = (int) ($distribution[$star] ?? 0);
        }

        $reviews = Review::query()
            ->where('restaurant_id', $restaurantId)
            ->where('is_visible', true)
            ->with(['user', 'order'])
            ->when($request->filled('rating'), fn ($q) => $q->where('rating', (int) $request->rating))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search')->trim().'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('comment', 'like', $term)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('restaurant.reviews.index', compact('reviews', 'summary', 'starCounts'));
    }
}
