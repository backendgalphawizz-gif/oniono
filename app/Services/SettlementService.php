<?php

namespace App\Services;

use App\Enums\SettlementStatus;
use App\Models\Restaurant;
use App\Models\RestaurantEarning;
use App\Models\Settlement;
use App\Models\SettlementItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SettlementService
{
    public function pendingEarnings(Restaurant $restaurant): Collection
    {
        return RestaurantEarning::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('status', 'pending')
            ->whereDoesntHave('settlementItem')
            ->with('order')
            ->orderByDesc('created_at')
            ->get();
    }

    public function createSettlement(
        Restaurant $restaurant,
        array $earningIds,
        float $amount,
        int $adminId,
        array $meta = []
    ): Settlement {
        $earnings = RestaurantEarning::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('id', $earningIds)
            ->where('status', 'pending')
            ->whereDoesntHave('settlementItem')
            ->lockForUpdate()
            ->get();

        if ($earnings->isEmpty()) {
            throw ValidationException::withMessages([
                'earnings' => 'No eligible earnings selected.',
            ]);
        }

        $calculated = round($earnings->sum('net_earning'), 2);

        if (abs($calculated - $amount) > 0.01) {
            throw ValidationException::withMessages([
                'amount' => "Settlement amount must match selected earnings (₹{$calculated}).",
            ]);
        }

        return DB::transaction(function () use ($restaurant, $earnings, $amount, $adminId, $meta) {
            $settlement = Settlement::query()->create([
                'settlement_number' => 'STL'.now()->format('ymdHis').random_int(100, 999),
                'restaurant_id' => $restaurant->id,
                'processed_by_admin_id' => $adminId,
                'amount' => $amount,
                'payment_mode' => $meta['payment_mode'] ?? null,
                'transaction_reference' => $meta['transaction_reference'] ?? null,
                'payment_date' => $meta['payment_date'] ?? now()->toDateString(),
                'admin_remarks' => $meta['admin_remarks'] ?? null,
                'status' => SettlementStatus::Paid,
            ]);

            foreach ($earnings as $earning) {
                SettlementItem::query()->create([
                    'settlement_id' => $settlement->id,
                    'restaurant_earning_id' => $earning->id,
                    'amount' => $earning->net_earning,
                ]);

                $earning->update([
                    'status' => 'settled',
                    'settled_at' => now(),
                ]);
            }

            $settlement->load('items');
            app(RestaurantNotificationService::class)->settlementUpdate($settlement);

            return $settlement;
        });
    }
}
