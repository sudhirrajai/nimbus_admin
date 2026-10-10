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

        $billingCycle = $request->input('billing_cycle', 'yearly');
        $amountInr = $plan->price_inr;
        if ($billingCycle === 'monthly' && !empty($plan->monthly_price_inr) && $plan->monthly_price_inr > 0) {
            $amountInr = $plan->monthly_price_inr;
        }

        $api = new Api($this->razorpayId, $this->razorpayKey);

        $orderData = [
            'receipt'         => 'rcpt_' . Auth::id() . '_' . time(),
            'amount'          => (int) round($amountInr * 100), // amount in paise
            'currency'        => 'INR',
            'notes'           => [
                'plan' => $plan->slug,
                'user_id' => Auth::id(),
                'billing_cycle' => $billingCycle,
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
                $billingCycle = $order->notes->billing_cycle ?? 'yearly';
                $expiresAt = ($billingCycle === 'monthly') ? now()->addMonth() : now()->addYear();

                $license = License::create([
                    'user_id' => Auth::id(),
                    'license_key' => License::generateKey($plan),
                    'plan' => $plan,
                    'status' => 'active',
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_order_id' => $request->razorpay_order_id,
                    'expires_at' => $expiresAt,
                    'status_changed_at' => now(),
                ]);
            }

            // Generate Invoice if not exists
            $existingInvoice = \App\Models\Invoice::where('payment_id', $paymentId)->first();
            if (!$existingInvoice) {
                $planModel = \App\Models\Plan::where('slug', $plan)->first();
                $billingCycle = $order->notes->billing_cycle ?? 'yearly';
                $amount = $order->amount ? ($order->amount / 100) : ($planModel ? $planModel->price_inr : 0);
                $periodLabel = ($billingCycle === 'monthly') ? 'Monthly Subscription' : 'Annual Subscription';

                \App\Models\Invoice::create([
                    'user_id' => Auth::id(),
                    'invoice_number' => \App\Models\Invoice::generateInvoiceNumber(),
                    'type' => 'license_plan',
                    'plan_name' => $planModel->name ?? (ucfirst($plan) . ' Plan'),
                    'description' => 'Nimbus ' . ($planModel->name ?? ucfirst($plan)) . ' Server License (' . $periodLabel . ')',
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
                        'billing_cycle' => $billingCycle,
                    ],
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Payment successful! Your ' . ucfirst($plan) . ' license and invoice have been generated.');

        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Initiate Razorpay payment for frontend Managed Hosting plans.
     */
    public function initiateHostingPayment(Request $request)
    {
        $request->validate([
            'plan' => 'required|string',
            'billing_cycle' => 'nullable|string|in:monthly,yearly',
            'currency' => 'nullable|string|in:INR,USD',
            'domain_choice' => 'nullable|string|in:have,later',
            'domain' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'company_name' => 'nullable|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:30',
            'country' => 'required|string|max:100',
            'tax_id' => 'nullable|string|max:50',
        ]);

        $planSlug = $request->plan;
        $plan = \App\Models\Plan::where(function ($q) use ($planSlug) {
            $q->where('slug', $planSlug)
              ->orWhere('id', $planSlug)
              ->orWhere('name', 'LIKE', "%{$planSlug}%");
        })->where('is_active', true)->first();

        if (!$plan) {
            return response()->json(['message' => 'Invalid hosting package selected.'], 422);
        }

        $billingCycle = $request->input('billing_cycle', 'yearly');

        if ($billingCycle === 'monthly') {
            $amountInr = !empty($plan->monthly_price_inr) ? (float)$plan->monthly_price_inr : round((float)$plan->price_inr / 10);
        } else {
            $amountInr = (float)$plan->price_inr;
        }

        if ($amountInr <= 0) {
            $amountInr = ($billingCycle === 'monthly') ? 390 : 3800;
        }

        $api = new Api($this->razorpayId, $this->razorpayKey);

        $domainChoice = $request->input('domain_choice', 'have');
        $domain = $request->input('domain');
        if ($domainChoice === 'later' || empty($domain)) {
            $domain = 'pending-setup-' . strtolower(\Illuminate\Support\Str::random(6)) . '.roook.host';
        }

        $orderData = [
            'receipt'  => 'host_' . (Auth::id() ?? 'guest') . '_' . time(),
            'amount'   => (int) round($amountInr * 100), // paise
            'currency' => 'INR',
            'notes'    => [
                'order_type'     => 'hosting',
                'plan_id'        => (string) $plan->id,
                'plan_slug'      => (string) $plan->slug,
                'plan_name'      => substr((string) $plan->name, 0, 40),
                'billing_cycle'  => (string) $billingCycle,
                'domain_choice'  => (string) $domainChoice,
                'domain'         => substr((string) $domain, 0, 80),
                'customer_name'  => substr((string) $request->name, 0, 80),
                'customer_email' => substr((string) $request->email, 0, 80),
                'customer_phone' => substr((string) $request->phone, 0, 30),
                'company_name'   => substr((string) ($request->company_name ?? ''), 0, 80),
                'address'        => substr((string) $request->address, 0, 100),
                'city'           => substr((string) $request->city, 0, 50),
                'state'          => substr((string) $request->state, 0, 50),
                'country'        => substr((string) $request->country, 0, 50),
            ],
        ];

        $razorpayOrder = $api->order->create($orderData);

        return response()->json([
            'order_id' => $razorpayOrder['id'],
            'amount'   => $razorpayOrder['amount'],
            'key_id'   => $this->razorpayId,
            'plan'     => $plan->slug,
            'plan_name'=> $plan->name,
            'user'     => [
                'name'  => $request->name,
                'email' => $request->email,
            ],
        ]);
    }

    /**
     * Verify payment and place Managed Hosting account into PENDING state for max 2 hours verification.
     */
    public function verifyHostingPayment(Request $request)
    {
        $api = new Api($this->razorpayId, $this->razorpayKey);

        try {
            $attributes = [
                'razorpay_order_id'   => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature'  => $request->razorpay_signature,
            ];

            $api->utility->verifyPaymentSignature($attributes);

            $order = $api->order->fetch($request->razorpay_order_id);
            $notes = $order->notes;
            $paymentId = $request->razorpay_payment_id;

            // Idempotency check: if invoice for this payment already exists, redirect to confirmation
            $existingInvoice = \App\Models\Invoice::where('payment_id', $paymentId)->first();
            if ($existingInvoice && $existingInvoice->hosting_account_id) {
                $account = \App\Models\HostingAccount::find($existingInvoice->hosting_account_id);
                if ($account) {
                    return redirect()->route('hosting.order-confirmation', $account->uuid);
                }
            }

            // Authenticate or register customer with full address details
            $user = Auth::user();
            $isNewUser = false;
            if (!$user) {
                $email = $request->input('email', $notes->customer_email ?? null);
                $name = $request->input('name', $notes->customer_name ?? 'Customer');

                $user = \App\Models\User::where('email', $email)->first();
                if (!$user) {
                    $user = \App\Models\User::create([
                        'name' => $name,
                        'email' => $email,
                        'phone' => $request->input('phone', $notes->customer_phone ?? null),
                        'company_name' => $request->input('company_name', $notes->company_name ?? null),
                        'address' => $request->input('address', $notes->address ?? null),
                        'city' => $request->input('city', $notes->city ?? null),
                        'state' => $request->input('state', $notes->state ?? null),
                        'postal_code' => $request->input('postal_code', null),
                        'country' => $request->input('country', $notes->country ?? 'India'),
                        'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
                        'role' => 'user',
                    ]);
                    $isNewUser = true;
                }
                Auth::login($user);
            }

            // Sync user billing profile with latest details
            $profileUpdates = array_filter([
                'phone' => $request->input('phone', $notes->customer_phone ?? null),
                'company_name' => $request->input('company_name', $notes->company_name ?? null),
                'address' => $request->input('address', $notes->address ?? null),
                'city' => $request->input('city', $notes->city ?? null),
                'state' => $request->input('state', $notes->state ?? null),
                'postal_code' => $request->input('postal_code', null),
                'country' => $request->input('country', $notes->country ?? null),
            ]);
            if (!empty($profileUpdates)) {
                $user->update($profileUpdates);
            }

            $planSlug = $notes->plan_slug ?? $request->plan;
            $plan = \App\Models\Plan::where('slug', $planSlug)->orWhere('id', $planSlug)->first();
            $billingCycle = $notes->billing_cycle ?? $request->billing_cycle ?? 'yearly';
            $domain = $notes->domain ?? $request->domain;
            $domainChoice = $notes->domain_choice ?? $request->domain_choice ?? 'have';

            if (empty($domain)) {
                $domain = 'pending-setup-' . strtolower(\Illuminate\Support\Str::random(6)) . '.roook.host';
            }

            $amountInr = ($order->amount / 100);
            $renewalPrice = ($billingCycle === 'monthly')
                ? ($plan?->renewal_monthly_price_inr ?? $plan?->monthly_price_inr ?? $amountInr)
                : ($plan?->renewal_price_inr ?? $plan?->price_inr ?? $amountInr);

            $startsAt = now();
            $renewsAt = ($billingCycle === 'monthly') ? now()->addMonth() : now()->addYear();

            // Create HostingAccount in PENDING state (awaiting admin verification max 2 hrs)
            $account = \App\Models\HostingAccount::create([
                'user_id' => $user->id,
                'primary_domain' => $domain,
                'domain' => $domain,
                'package_name' => $plan ? $plan->name : 'Starter Cloud',
                'plan_name' => $plan ? $plan->name : 'Starter Cloud',
                'billing_cycle' => $billingCycle,
                'initial_price' => $amountInr,
                'renewal_price' => $renewalPrice,
                'currency' => 'INR',
                'status' => 'pending', // Under pending state for admin verification for max 2 hrs
                'starts_at' => $startsAt,
                'renews_at' => $renewsAt,
                'auto_invoice' => true,
                'renewal_invoice_days' => 14,
                'notes' => "Order verified via Razorpay ({$paymentId}). Status: Pending verification & server container provisioning (max 2 hrs target). Domain setup: {$domainChoice}",
            ]);

            // Create Invoice with comprehensive professional billing details
            $invoice = \App\Models\Invoice::create([
                'user_id' => $user->id,
                'invoice_number' => \App\Models\Invoice::generateInvoiceNumber(),
                'type' => 'hosting_plan',
                'plan_name' => $plan ? $plan->name : 'Managed Cloud',
                'description' => "Managed Cloud Hosting - {$domain} ({$account->plan_name}, " . ($billingCycle === 'monthly' ? 'Monthly' : 'Annual') . ")",
                'amount' => $amountInr,
                'currency' => 'INR',
                'status' => 'paid',
                'payment_method' => 'Razorpay',
                'payment_id' => $paymentId,
                'paid_at' => now(),
                'due_date' => $startsAt,
                'period_start' => $startsAt,
                'period_end' => $renewsAt,
                'hosting_account_id' => $account->id,
                'billing_details' => [
                    'customer_name' => $user->name,
                    'customer_email' => $user->email,
                    'customer_phone' => $request->input('phone', $notes->customer_phone ?? $user->phone),
                    'company_name' => $request->input('company_name', $notes->company_name ?? $user->company_name),
                    'address' => $request->input('address', $notes->address ?? $user->address),
                    'city' => $request->input('city', $notes->city ?? $user->city),
                    'state' => $request->input('state', $notes->state ?? $user->state),
                    'postal_code' => $request->input('postal_code', $user->postal_code),
                    'country' => $request->input('country', $notes->country ?? ($user->country ?: 'India')),
                    'tax_id' => $request->input('tax_id'),
                    'order_id' => $request->razorpay_order_id,
                    'domain' => $domain,
                    'billing_cycle' => $billingCycle,
                    'transaction_id' => $paymentId,
                ],
            ]);

            // Dispatch Account Verification link email if the user is new or unverified
            if ($isNewUser || !$user->hasVerifiedEmail()) {
                try {
                    $user->sendEmailVerificationNotification();
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to dispatch email verification: ' . $e->getMessage());
                }
            }

            // Dispatch official Invoice & Payment receipt email
            try {
                $user->notify(new \App\Notifications\InvoiceNotification($invoice));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to dispatch invoice notification: ' . $e->getMessage());
            }

            return redirect()->route('hosting.order-confirmation', $account->uuid)
                ->with('success', 'Payment successful! Your hosting account is under verification and server setup (typically within 2 hours).');

        } catch (\Exception $e) {
            return redirect()->route('checkout')
                ->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }
}

