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
                    License::create([
                        'user_id' => $userId,
                        'license_key' => License::generateKey($plan),
                        'plan' => $plan,
                        'status' => 'active',
                        'razorpay_payment_id' => $paymentId,
                        'razorpay_order_id' => $orderId,
                        'expires_at' => now()->addYear(),
                    ]);
                    
                    Log::info("License generated via Webhook for Payment ID: {$paymentId}");
                }
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error("Razorpay Webhook Error: " . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }
    }
}
