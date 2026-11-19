<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;

class CodPaymentHandler
{
    public function initialize(Order $order): Payment
    {
        $payment = Payment::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'method' => PaymentMethod::Cod->value,
                'status' => PaymentStatus::Pending,
                'amount' => $order->grand_total,
                'cod_collection_status' => 'pending',
                'cod_remittance_status' => 'pending',
                'cod_reconciliation_status' => 'pending',
            ]
        );

        $order->update([
            'payment_method' => PaymentMethod::Cod,
            'payment_status' => PaymentStatus::Pending,
        ]);

        return $payment;
    }

    public function markCollected(Payment $payment, float $amount, string $collectorType, int $collectorId): Payment
    {
        $payment->update([
            'cod_collection_status' => 'collected',
            'cod_collected_amount' => $amount,
            'cod_collected_at' => now(),
            'cod_collected_by_type' => $collectorType,
            'cod_collected_by_id' => $collectorId,
            'status' => PaymentStatus::Success,
        ]);

        $payment->order->update(['payment_status' => PaymentStatus::Success]);

        return $payment->fresh();
    }
}
