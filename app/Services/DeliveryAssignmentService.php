<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryPartner;
use App\Models\DeliveryStatusHistory;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryAssignmentService
{
    public function assign(Order $order, DeliveryPartner $partner, int $adminId, ?string $note = null): DeliveryAssignment
    {
        if ($order->status !== OrderStatus::ReadyForPickup) {
            throw ValidationException::withMessages([
                'order' => 'Order must be ready for pickup before assignment.',
            ]);
        }

        if (
            ! $partner->is_active
            || $partner->status !== 'approved'
            || $partner->is_blocked
            || $partner->is_suspended
        ) {
            throw ValidationException::withMessages([
                'delivery_partner' => 'Selected delivery partner is not eligible.',
            ]);
        }

        return DB::transaction(function () use ($order, $partner, $adminId, $note) {
            DeliveryAssignment::query()
                ->where('order_id', $order->id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->update(['status' => 'reassigned']);

            $assignment = DeliveryAssignment::query()->create([
                'order_id' => $order->id,
                'delivery_partner_id' => $partner->id,
                'assigned_by_admin_id' => $adminId,
                'status' => 'assigned',
                'note' => $note,
                'assigned_at' => now(),
            ]);

            DeliveryStatusHistory::query()->create([
                'delivery_assignment_id' => $assignment->id,
                'status' => 'assigned',
                'note' => $note,
            ]);

            return $assignment;
        });
    }
}
