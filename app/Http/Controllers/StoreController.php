<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreController extends Controller
{
    /**
     * Display the Store / Packages catalogue.
     */
    public function index()
    {
        $managedPlans = Plan::where('is_active', true)
            ->managedHosting()
            ->orderBy('price_inr')
            ->get();

        $selfHostedPlans = Plan::where('is_active', true)
            ->selfHosted()
            ->orderBy('price_inr')
            ->get();

        return Inertia::render('Store/Index', [
            'managedPlans' => $managedPlans,
            'selfHostedPlans' => $selfHostedPlans,
        ]);
    }
}
