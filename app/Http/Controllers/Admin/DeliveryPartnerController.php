<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\DeliveryDocument;
use App\Models\DeliveryPartner;
use App\Models\Review;
use App\Models\Setting;
use App\Services\CommissionOverrideService;
use App\Services\DeliveryPartnerDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DeliveryPartnerController extends Controller
{
    public function index(Request $request)
    {
        $partners = DeliveryPartner::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search')->trim();
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('vehicle_number', 'like', "%{$search}%");
                });
            })
            ->when($request->registration === 'pending', fn ($q) => $q->where('status', 'pending'))
            ->when($request->registration === 'approved', fn ($q) => $q->where('status', 'approved'))
            ->when($request->registration === 'rejected', fn ($q) => $q->where('status', 'rejected'))
            ->when($request->account === 'active', fn ($q) => $q->where('is_blocked', false)->where('is_suspended', false))
            ->when($request->account === 'blocked', fn ($q) => $q->where('is_blocked', true))
            ->when($request->account === 'suspended', fn ($q) => $q->where('is_suspended', true))
            ->withCount('assignments')
            ->latest()
            ->paginate(admin_listing_per_page())
            ->withQueryString();

        return view('admin.delivery-partners.index', compact('partners'));
    }

    public function create()
    {
        return view('admin.delivery-partners.create');
    }

    public function store(Request $request, DeliveryPartnerDocumentService $documents)
    {
        $data = $this->validated($request, requireDocuments: true);

        $partner = DeliveryPartner::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'vehicle_type' => $data['vehicle_type'] ?? null,
            'vehicle_number' => $data['vehicle_number'] ?? null,
            'status' => 'pending',
            'is_active' => false,
        ]);

        $documents->storeFromRequest($request, $partner, true);

        return redirect()->route('admin.delivery-partners.show', $partner)->with('success', 'Delivery partner created. Verify documents before approval.');
    }

    public function show(DeliveryPartner $deliveryPartner)
    {
        $deliveryPartner->load(['documents', 'activeCommission']);
        $defaultDeliveryCommission = Setting::getValue('default_delivery_partner_commission_percentage', 15);

        $assignedDeliveries = $deliveryPartner->assignments()
            ->with(['order.restaurant'])
            ->whereHas('order', fn ($q) => $q->where('status', '!=', OrderStatus::Delivered))
            ->latest('assigned_at')
            ->limit(25)
            ->get();

        $completedDeliveries = $deliveryPartner->assignments()
            ->with(['order.restaurant'])
            ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::Delivered))
            ->latest('assigned_at')
            ->limit(25)
            ->get();

        $earnings = $deliveryPartner->earnings()
            ->with('order')
            ->latest()
            ->limit(25)
            ->get();

        $earningsSummary = [
            'total' => $deliveryPartner->earnings()->sum('amount'),
            'paid' => $deliveryPartner->earnings()->where('status', 'paid')->sum('amount'),
            'pending' => $deliveryPartner->earnings()->where('status', 'pending')->sum('amount'),
        ];

        $reviewsEnabled = (bool) Setting::getValue('delivery_partner_reviews_enabled', false);
        $reviews = collect();

        if ($reviewsEnabled) {
            $orderIds = $deliveryPartner->assignments()
                ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::Delivered))
                ->pluck('order_id');

            $reviews = Review::query()
                ->with(['restaurant', 'order'])
                ->whereIn('order_id', $orderIds)
                ->where('is_visible', true)
                ->latest()
                ->limit(25)
                ->get();
        }

        return view('admin.delivery-partners.show', compact(
            'deliveryPartner',
            'assignedDeliveries',
            'completedDeliveries',
            'earnings',
            'earningsSummary',
            'reviews',
            'reviewsEnabled',
            'defaultDeliveryCommission',
        ));
    }

    public function edit(DeliveryPartner $deliveryPartner)
    {
        $deliveryPartner->load(['activeCommission', 'documents']);
        $masterCommission = Setting::getValue('default_delivery_partner_commission_percentage', 15);

        return view('admin.delivery-partners.edit', compact('deliveryPartner', 'masterCommission'));
    }

    public function update(Request $request, DeliveryPartner $deliveryPartner, DeliveryPartnerDocumentService $documents)
    {
        $data = $this->validated($request, updating: true, partner: $deliveryPartner);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'vehicle_type' => $data['vehicle_type'] ?? null,
            'vehicle_number' => $data['vehicle_number'] ?? null,
            'is_available' => $request->boolean('is_available'),
            'is_active' => $request->boolean('is_active'),
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $deliveryPartner->update($payload);

        $documents->storeFromRequest($request, $deliveryPartner);

        if ($request->has('commission_use_master') || $request->filled('commission_percentage')) {
            if ($request->has('commission_use_master') && ! $request->boolean('commission_use_master')) {
                $request->validate([
                    'commission_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
                ]);
            }
            app(CommissionOverrideService::class)->applyDeliveryPartnerFromRequest($request, $deliveryPartner);
        }

        return redirect()->route('admin.delivery-partners.show', $deliveryPartner)->with('success', 'Delivery partner updated.');
    }

    public function destroy(DeliveryPartner $deliveryPartner)
    {
        $deliveryPartner->delete();

        return redirect()->route('admin.delivery-partners.index')->with('success', 'Delivery partner removed.');
    }

    public function approve(DeliveryPartner $deliveryPartner, DeliveryPartnerDocumentService $documents)
    {
        if (! $documents->hasAllRequiredVerified($deliveryPartner)) {
            $message = $documents->hasAllRequiredUploaded($deliveryPartner)
                ? 'Approve only after all required documents are verified.'
                : 'Missing documents: '.implode(', ', $documents->missingRequiredLabels($deliveryPartner)).'.';

            return back()->withErrors(['approve' => $message]);
        }

        $deliveryPartner->update([
            'status' => 'approved',
            'is_active' => true,
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Registration approved.');
    }

    public function reject(Request $request, DeliveryPartner $deliveryPartner)
    {
        $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);

        $deliveryPartner->update([
            'status' => 'rejected',
            'is_active' => false,
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Registration rejected.');
    }

    public function block(DeliveryPartner $deliveryPartner)
    {
        $deliveryPartner->update(['is_blocked' => true, 'is_available' => false]);

        return back()->with('success', 'Partner blocked.');
    }

    public function unblock(DeliveryPartner $deliveryPartner)
    {
        $deliveryPartner->update(['is_blocked' => false]);

        return back()->with('success', 'Partner unblocked.');
    }

    public function suspend(DeliveryPartner $deliveryPartner)
    {
        $deliveryPartner->update(['is_suspended' => true, 'is_available' => false]);

        return back()->with('success', 'Partner suspended.');
    }

    public function unsuspend(DeliveryPartner $deliveryPartner)
    {
        $deliveryPartner->update(['is_suspended' => false]);

        return back()->with('success', 'Suspension lifted.');
    }

    public function verifyDocument(DeliveryDocument $document)
    {
        $document->update(['verification_status' => 'verified']);

        return back()->with('success', 'Document verified.');
    }

    public function rejectDocument(Request $request, DeliveryDocument $document)
    {
        $request->validate(['note' => ['nullable', 'string', 'max:500']]);

        $document->update([
            'verification_status' => 'rejected',
            'admin_note' => $request->note,
        ]);

        return back()->with('success', 'Document marked as rejected.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, bool $updating = false, ?DeliveryPartner $partner = null, bool $requireDocuments = false): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('delivery_partners', 'email')->ignore($partner?->id),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('delivery_partners', 'phone')->ignore($partner?->id),
            ],
            'password' => [$updating ? 'nullable' : 'required', 'string', 'min:8'],
            'vehicle_type' => ['nullable', 'string', 'max:100'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
        ];

        $docRules = $requireDocuments
            ? app(DeliveryPartnerDocumentService::class)->requiredValidationRules()
            : app(DeliveryPartnerDocumentService::class)->optionalUpdateValidationRules();

        return $request->validate($rules + $docRules);
    }
}
