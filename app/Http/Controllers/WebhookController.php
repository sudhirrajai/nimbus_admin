<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\User;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleRazorpay(Request $request)
    {
        $webhookSecret = env('RAZORPAY_WEBHOOK_SECRET');
        $signature = $request->header('X-Razorpay-Signature');
        
        $api = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET'));

        try {
            // Verify signature if secret is set
            if ($webhookSecret) {
                $api->utility->verifyWebhookSignature($request->getContent(), $signature, $webhookSecret);
            }

            $payload = $request->all();
            $event = $payload['event'];

            if ($event === 'payment.captured') {
                $payment = $payload['payload']['payment']['entity'];
                $orderId = $payment['order_id'];
                $paymentId = $payment['id'];
                
                // Fetch order to get notes (plan and user_id)
                $order = $api->order->fetch($orderId);
                $plan = $order->notes->plan ?? 'pro';
                $userId = $order->notes->user_id;

                // Check if license already exists for this payment
                $existing = License::where('razorpay_payment_id', $paymentId)->first();
                
                if (!$existing) {
                    $license = License::create([
                        'user_id' => $userId,
                        'license_key' => License::generateKey($plan),
                        'plan' => $plan,
                        'status' => 'active',
                        'razorpay_payment_id' => $paymentId,
                        'razorpay_order_id' => $orderId,
                        'expires_at' => now()->addYear(),
                        'status_changed_at' => now(),
                    ]);
                    
                    Log::info("License generated via Webhook for Payment ID: {$paymentId}");

                    // Check and create invoice
                    $existingInvoice = \App\Models\Invoice::where('payment_id', $paymentId)->first();
                    if (!$existingInvoice) {
                        $user = User::find($userId);
                        $planModel = \App\Models\Plan::where('slug', $plan)->first();
                        $amount = $planModel ? $planModel->price_inr : (($payment['amount'] ?? 0) / 100);
                        \App\Models\Invoice::create([
                            'user_id' => $userId,
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
                                'customer_name' => $user->name ?? 'Customer',
                                'customer_email' => $user->email ?? '',
                                'order_id' => $orderId,
                            ],
                        ]);
                    }
                }
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error("Razorpay Webhook Error: " . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }
    }
}
