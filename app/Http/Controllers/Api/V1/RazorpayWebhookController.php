<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Payment\RazorpayGateway;
use Illuminate\Http\Request;

class RazorpayWebhookController extends Controller
{
    public function __invoke(Request $request, RazorpayGateway $gateway)
    {
        $gateway->handleWebhook($request->all());

        return response()->json(['status' => 'ok']);
    }
}
