<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page for Managed Cloud Hosting plans.
     */
    public function index(Request $request)
    {
        $managedPlans = Plan::where('is_active', true)
            ->managedHosting()
            ->orderBy('price_usd')
            ->get();

        return Inertia::render('Checkout/Index', [
            'managedPlans' => $managedPlans,
            'initialPlan' => $request->query('plan', 'starter-cloud'),
            'initialBilling' => $request->query('billing', 'yearly'),
            'initialCurrency' => $request->query('currency', 'INR'),
            'razorpayKey' => env('RAZORPAY_KEY_ID'),
            'datacenters' => \App\Http\Controllers\Admin\AdminSettingsController::getActiveDatacenters(),
            'user' => Auth::user(),
        ]);
    }
}
