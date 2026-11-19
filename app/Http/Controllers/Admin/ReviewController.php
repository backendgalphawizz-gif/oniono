<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesAdminGeoScope;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use AppliesAdminGeoScope;

    public function index(Request $request)
    {
        $reviews = $this->scopeReviews(Review::query())
            ->with(['restaurant:id,name', 'user:id,name,email,phone', 'order:id,order_number'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search')->trim();
                $q->where(function ($inner) use ($search) {
                    $inner->where('comment', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('restaurant', fn ($r) => $r->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('rating'), fn ($q) => $q->where('rating', (int) $request->input('rating')))
            ->when($request->visibility === 'visible', fn ($q) => $q->where('is_visible', true))
            ->when($request->visibility === 'hidden', fn ($q) => $q->where('is_visible', false))
            ->latest()
            ->paginate(admin_listing_per_page())
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function update(Request $request, Review $review)
    {
        $this->ensureReviewInScope($review);
        $review->update(['is_visible' => $request->boolean('is_visible')]);

        return back()->with('success', 'Review updated.');
    }

    public function destroy(Review $review)
    {
        $this->ensureReviewInScope($review);
        $review->delete();

        return back()->with('success', 'Review deleted.');
    }
}
