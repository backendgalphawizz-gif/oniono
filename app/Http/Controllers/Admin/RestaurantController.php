<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\RestaurantStatus;
use App\Http\Controllers\Admin\Concerns\AppliesAdminGeoScope;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantDocument;
use App\Models\RestaurantUser;
use App\Models\Setting;
use App\Services\CommissionOverrideService;
use App\Services\RestaurantDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RestaurantController extends Controller
{
    use AppliesAdminGeoScope;

    public function index(Request $request)
    {
        $restaurants = $this->scopeRestaurants(Restaurant::query())
            ->with('user')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search')->trim();
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('owner_name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->account === 'blocked', fn ($q) => $q->where('is_blocked', true))
            ->when($request->account === 'active', fn ($q) => $q->where('is_blocked', false)->where('status', RestaurantStatus::Approved))
            ->withCount(['orders', 'foodItems'])
            ->latest()
            ->paginate(admin_listing_per_page())
            ->withQueryString();

        return view('admin.restaurants.index', compact('restaurants'));
    }

    public function create()
    {
        return view('admin.restaurants.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if (! $this->geoScope()->allowsLocation($data['state'], $data['city'] ?? null)) {
            return back()
                ->withInput()
                ->withErrors(['state' => 'You cannot create restaurants outside your assigned region.']);
        }
        $password = $data['password'];
        unset($data['password']);
        $data['gst_enabled'] = $request->boolean('gst_enabled');

        DB::transaction(function () use ($data, $password, $request) {
            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store('restaurants/logos', 'public');
            }

            $data['status'] = RestaurantStatus::Approved;
            $restaurant = Restaurant::query()->create($data);

            RestaurantUser::query()->create([
                'restaurant_id' => $restaurant->id,
                'name' => $data['owner_name'],
                'email' => $data['email'],
                'password' => Hash::make($password),
            ]);
        });

        return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant created.');
    }

    public function show(Restaurant $restaurant)
    {
        $this->ensureRestaurantInScope($restaurant);
        $restaurant->load(['user', 'documents', 'images', 'activeCommission']);

        $menuItems = $restaurant->foodItems()
            ->with('category')
            ->latest()
            ->limit(50)
            ->get();

        $orders = $restaurant->orders()
            ->latest()
            ->limit(25)
            ->get();

        $salesSummary = [
            'orders' => $restaurant->orders()->count(),
            'revenue' => $restaurant->orders()->sum('grand_total'),
            'delivered' => $restaurant->orders()->where('status', OrderStatus::Delivered)->count(),
        ];

        $reviews = $restaurant->reviews()
            ->latest()
            ->limit(25)
            ->get();

        $averageRating = $restaurant->reviews()->where('is_visible', true)->avg('rating');

        $earnings = $restaurant->earnings()
            ->with('order')
            ->latest()
            ->limit(25)
            ->get();

        $earningsSummary = [
            'total' => $restaurant->earnings()->sum('net_earning'),
            'commission' => $restaurant->earnings()->sum('commission_amount'),
            'settled' => $restaurant->earnings()->where('status', 'settled')->sum('net_earning'),
            'pending' => $restaurant->earnings()->where('status', 'pending')->sum('net_earning'),
        ];

        $defaultCommission = Setting::getValue('default_commission_percentage', 10);

        return view('admin.restaurants.show', compact(
            'restaurant',
            'menuItems',
            'orders',
            'salesSummary',
            'reviews',
            'averageRating',
            'earnings',
            'earningsSummary',
            'defaultCommission',
        ));
    }

    public function edit(Restaurant $restaurant)
    {
        $this->ensureRestaurantInScope($restaurant);
        $restaurant->load(['activeCommission', 'documents']);
        $masterCommission = Setting::getValue('default_commission_percentage', 10);

        return view('admin.restaurants.edit', compact('restaurant', 'masterCommission'));
    }

    public function update(Request $request, Restaurant $restaurant)
    {
        $this->ensureRestaurantInScope($restaurant);
        $data = $this->validated($request, updating: true, restaurant: $restaurant);
        if (! $this->geoScope()->allowsLocation($data['state'], $data['city'] ?? null)) {
            return back()
                ->withInput()
                ->withErrors(['state' => 'You cannot move this restaurant outside your assigned region.']);
        }
        unset($data['password']);
        $data['gst_enabled'] = $request->boolean('gst_enabled');

        if ($request->hasFile('logo')) {
            if ($restaurant->logo) {
                Storage::disk('public')->delete($restaurant->logo);
            }
            $data['logo'] = $request->file('logo')->store('restaurants/logos', 'public');
        }

        $restaurant->update($data);

        $request->validate(app(RestaurantDocumentService::class)->optionalUpdateValidationRules());
        app(RestaurantDocumentService::class)->storeFromRequest($request, $restaurant);

        if ($request->has('commission_use_master') || $request->filled('commission_percentage')) {
            if ($request->has('commission_use_master') && ! $request->boolean('commission_use_master')) {
                $request->validate([
                    'commission_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
                ]);
            }
            app(CommissionOverrideService::class)->applyRestaurantFromRequest($request, $restaurant);
        }

        if ($request->filled('password') && $restaurant->user) {
            $restaurant->user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.restaurants.show', $restaurant)->with('success', 'Restaurant updated.');
    }

    public function destroy(Restaurant $restaurant)
    {
        $this->ensureRestaurantInScope($restaurant);
        $restaurant->delete();

        return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant removed.');
    }

    public function approve(Restaurant $restaurant, RestaurantDocumentService $documents)
    {
        $this->ensureRestaurantInScope($restaurant);
        if (! $documents->hasAllRequiredVerified($restaurant)) {
            $message = $documents->hasAllRequiredUploaded($restaurant)
                ? 'Approve only after all required documents are verified.'
                : 'Missing documents: '.implode(', ', $documents->missingRequiredLabels($restaurant)).'.';

            return back()->withErrors(['approve' => $message]);
        }

        $restaurant->update([
            'status' => RestaurantStatus::Approved,
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Restaurant approved.');
    }

    public function reject(Request $request, Restaurant $restaurant)
    {
        $this->ensureRestaurantInScope($restaurant);
        $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);
        $restaurant->update([
            'status' => RestaurantStatus::Rejected,
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Restaurant rejected.');
    }

    public function suspend(Restaurant $restaurant)
    {
        $this->ensureRestaurantInScope($restaurant);
        $restaurant->update(['status' => RestaurantStatus::Suspended, 'is_open' => false]);

        return back()->with('success', 'Restaurant suspended.');
    }

    public function activate(Restaurant $restaurant)
    {
        $this->ensureRestaurantInScope($restaurant);
        $restaurant->update(['status' => RestaurantStatus::Approved]);

        return back()->with('success', 'Restaurant activated.');
    }

    public function block(Restaurant $restaurant)
    {
        $this->ensureRestaurantInScope($restaurant);
        $restaurant->update(['is_blocked' => true, 'is_open' => false]);

        return back()->with('success', 'Restaurant blocked.');
    }

    public function unblock(Restaurant $restaurant)
    {
        $this->ensureRestaurantInScope($restaurant);
        $restaurant->update(['is_blocked' => false]);

        return back()->with('success', 'Restaurant unblocked.');
    }

    public function verifyDocument(RestaurantDocument $document)
    {
        $document->loadMissing('restaurant');
        if ($document->restaurant) {
            $this->ensureRestaurantInScope($document->restaurant);
        }
        $document->update(['verification_status' => 'verified']);

        return back()->with('success', 'Document verified.');
    }

    public function rejectDocument(Request $request, RestaurantDocument $document)
    {
        $document->loadMissing('restaurant');
        if ($document->restaurant) {
            $this->ensureRestaurantInScope($document->restaurant);
        }
        $request->validate(['admin_note' => ['nullable', 'string', 'max:500']]);

        $document->update([
            'verification_status' => 'rejected',
            'admin_note' => $request->admin_note,
        ]);

        return back()->with('success', 'Document rejected.');
    }

    private function validated(Request $request, bool $updating = false, ?Restaurant $restaurant = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('restaurants', 'email')->ignore($restaurant?->id),
            ],
            'phone' => ['required', 'string', 'max:20'],
            'password' => [$updating ? 'nullable' : 'required', 'string', 'min:8'],
            'description' => ['nullable', 'string'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_ifsc' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:20'],
            'gst_enabled' => ['nullable', 'boolean'],
            'default_gst_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);
    }
}
