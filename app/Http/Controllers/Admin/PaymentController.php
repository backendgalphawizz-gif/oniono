<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesAdminGeoScope;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payment\CodPaymentHandler;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use AppliesAdminGeoScope;

    public function index(Request $request)
    {
        $payments = $this->scopePayments(Payment::query())
            ->with('order.restaurant')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->method, fn ($q, $m) => $q->where('method', $m))
            ->latest()
            ->paginate(admin_listing_per_page())
            ->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        $this->ensurePaymentInScope($payment);
        $payment->load(['order', 'transactions']);

        return view('admin.payments.show', compact('payment'));
    }

    public function markCodCollected(Request $request, Payment $payment, CodPaymentHandler $cod)
    {
        $this->ensurePaymentInScope($payment);
        $data = $request->validate([
            'collected_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $cod->markCollected($payment, (float) $data['collected_amount'], 'admin', auth('admin')->id());

        return back()->with('success', 'COD collection recorded.');
    }
}
