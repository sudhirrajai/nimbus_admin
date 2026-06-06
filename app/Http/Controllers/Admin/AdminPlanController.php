<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminPlanController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Plans/Index', [
            'plans' => Plan::orderBy('price_inr')->get()
        ]);
    }

    public function edit(Plan $plan)
    {
        return Inertia::render('Admin/Plans/Edit', [
            'plan' => $plan
        ]);
    }

    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price_inr' => 'required|integer|min:0',
            'price_usd' => 'required|integer|min:0',
            'billing_period' => 'required|string|max:255',
            'max_domains' => 'required|integer|min:1',
            'features' => 'required|array',
            'features.*' => 'required|string|max:255',
            'is_active' => 'required|boolean',
            'is_popular' => 'required|boolean',
            'cta_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $plan->update($request->only([
            'name',
            'price_inr',
            'price_usd',
            'billing_period',
            'max_domains',
            'features',
            'is_active',
            'is_popular',
            'cta_text',
            'description',
        ]));

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }
}
