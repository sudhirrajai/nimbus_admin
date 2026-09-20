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

            // Generate Invoice if not exists
            $existingInvoice = \App\Models\Invoice::where('payment_id', $paymentId)->first();
            if (!$existingInvoice) {
                $planModel = \App\Models\Plan::where('slug', $plan)->first();
                $amount = $planModel ? $planModel->price_inr : ($order->amount / 100);
                \App\Models\Invoice::create([
                    'user_id' => Auth::id(),
                    'invoice_number' => \App\Models\Invoice::generateInvoiceNumber(),
                    'type' => 'license_plan',
                    'plan_name' => $planModel->name ?? (ucfirst($plan) . ' Plan'),
                    'description' => 'Nimbus ' . ($planModel->name ?? ucfirst($plan)) . ' Server License (Annual Subscription)',
                    'amount' => $amount,
                    'currency' => 'INR',
                    'status' => 'paid',
                    'payment_method' => 'Razorpay',
                    'payment_id' => $paymentId,
                    'paid_at' => now(),
                    'license_id' => $license->id,
                    'billing_details' => [
                        'customer_name' => Auth::user()->name,
                        'customer_email' => Auth::user()->email,
                        'order_id' => $request->razorpay_order_id,
                    ],
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Payment successful! Your ' . ucfirst($plan) . ' license and invoice have been generated.');


        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }
}
