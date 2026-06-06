<?php

namespace App\Http\Controllers;

use App\Models\License;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PaymentController extends Controller
{
    private $razorpayId;
    private $razorpayKey;

    public function __construct()
    {
        $this->razorpayId = env('RAZORPAY_KEY_ID');
        $this->razorpayKey = env('RAZORPAY_KEY_SECRET');
    }

    public function initiatePayment(Request $request)
    {
        $request->validate([
            'plan' => 'required|string',
        ]);

        $plan = \App\Models\Plan::where('slug', $request->plan)
            ->where('is_active', true)
            ->where('price_inr', '>', 0)
            ->first();

        if (!$plan) {
            return response()->json(['message' => 'Invalid plan requested.'], 422);
        }

        $api = new Api($this->razorpayId, $this->razorpayKey);

        $orderData = [
            'receipt'         => 'rcpt_' . Auth::id() . '_' . time(),
            'amount'          => $plan->price_inr * 100, // amount in paise
            'currency'        => 'INR',
            'notes'           => [
                'plan' => $plan->slug,
                'user_id' => Auth::id(),
            ]
        ];

        $razorpayOrder = $api->order->create($orderData);

        return response()->json([
            'order_id' => $razorpayOrder['id'],
            'amount' => $razorpayOrder['amount'],
            'key_id' => $this->razorpayId,
            'plan' => $plan->slug,
            'user' => [
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ]
        ]);
    }

    public function verifyPayment(Request $request)
    {
        $api = new Api($this->razorpayId, $this->razorpayKey);

        try {
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature
            ];

            $api->utility->verifyPaymentSignature($attributes);

            // Payment successful, generate license
            $order = $api->order->fetch($request->razorpay_order_id);
            $plan = $order->notes->plan;
            $paymentId = $request->razorpay_payment_id;

            // Check if license already exists (webhook might have already created it)
            $license = License::where('razorpay_payment_id', $paymentId)->first();

            if (!$license) {
                $license = License::create([
                    'user_id' => Auth::id(),
                    'license_key' => License::generateKey($plan),
                    'plan' => $plan,
                    'status' => 'active',
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_order_id' => $request->razorpay_order_id,
                    'expires_at' => now()->addYear(), // Standard 1 year expiry for paid plans
                    'status_changed_at' => now(),
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Payment successful! Your ' . ucfirst($plan) . ' license has been generated.');


        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }
}
