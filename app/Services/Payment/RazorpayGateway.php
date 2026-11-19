<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class RazorpayGateway
{
    public function isConfigured(): bool
    {
        return filled(config('services.razorpay.key')) && filled(config('services.razorpay.secret'));
    }

    public function createPaymentOrder(Order $order): Payment
    {
        $payment = Payment::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'method' => 'online',
                'status' => PaymentStatus::Pending,
                'amount' => $order->grand_total,
                'gateway' => 'razorpay',
            ]
        );

        if (! $this->isConfigured()) {
            $payment->update([
                'gateway_order_id' => 'demo_'.uniqid(),
                'meta' => ['mode' => 'sandbox_stub'],
            ]);

            return $payment->fresh();
        }

        $api = new \Razorpay\Api\Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );

        $razorpayOrder = $api->order->create([
            'receipt' => $order->order_number,
            'amount' => (int) round($order->grand_total * 100),
            'currency' => 'INR',
        ]);

        $payment->update(['gateway_order_id' => $razorpayOrder['id']]);

        return $payment->fresh();
    }

    public function verifyAndCapture(Payment $payment, array $payload): Payment
    {
        if ($this->isConfigured()) {
            $api = new \Razorpay\Api\Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $payload['razorpay_order_id'],
                'razorpay_payment_id' => $payload['razorpay_payment_id'],
                'razorpay_signature' => $payload['razorpay_signature'],
            ]);
        }

        $payment->update([
            'status' => PaymentStatus::Success,
            'gateway_payment_id' => $payload['razorpay_payment_id'] ?? null,
            'gateway_signature' => $payload['razorpay_signature'] ?? null,
        ]);

        $payment->order->update(['payment_status' => PaymentStatus::Success]);

        Transaction::query()->create([
            'payment_id' => $payment->id,
            'type' => 'capture',
            'amount' => $payment->amount,
            'status' => 'success',
            'reference' => $payload['razorpay_payment_id'] ?? null,
            'payload' => $payload,
        ]);

        return $payment->fresh();
    }

    public function handleWebhook(array $payload): void
    {
        Log::info('Razorpay webhook received', $payload);
    }
}
