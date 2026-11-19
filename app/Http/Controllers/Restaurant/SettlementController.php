<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\SettlementStatus;
use App\Http\Controllers\Controller;
use App\Models\Settlement;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function index(Request $request)
    {
        $restaurantId = restaurant_id();

        $summary = [
            'pending_amount' => (float) Settlement::query()
                ->where('restaurant_id', $restaurantId)
                ->whereIn('status', [SettlementStatus::Pending, SettlementStatus::Processing, SettlementStatus::OnHold])
                ->sum('amount'),
            'paid_amount' => (float) Settlement::query()
                ->where('restaurant_id', $restaurantId)
                ->where('status', SettlementStatus::Paid)
                ->sum('amount'),
        ];

        $settlements = Settlement::query()
            ->where('restaurant_id', $restaurantId)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('restaurant.settlements.index', compact('settlements', 'summary'));
    }

    public function show(Settlement $settlement)
    {
        abort_if($settlement->restaurant_id !== restaurant_id(), 403);
        $settlement->load(['items.earning.order']);

        return view('restaurant.settlements.show', compact('settlement'));
    }
}
