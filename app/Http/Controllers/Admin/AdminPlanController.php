<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminPlanController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Plans/Index', [
            'plans' => Plan::orderBy('type')->orderBy('price_inr')->get(),
            'available_modules' => Plan::AVAILABLE_MODULES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:self_hosted,managed_hosting',
            'slug' => 'nullable|string|max:255|unique:plans,slug',
            'price_inr' => 'required|numeric|min:0',
            'renewal_price_inr' => 'nullable|numeric|min:0',
            'price_usd' => 'required|numeric|min:0',
            'renewal_price_usd' => 'nullable|numeric|min:0',
            'billing_period' => 'required|string|max:255',
            'max_domains' => 'required|integer|min:1',
            'features' => 'required|array',
            'features.*' => 'required|string|max:255',
            'modules' => 'nullable|array',
            'modules.*' => 'string',
            'is_active' => 'required|boolean',
            'is_popular' => 'required|boolean',
            'cta_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if (empty($validated['slug'])) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Plan::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $validated['slug'] = $slug;
        }

        Plan::create($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'New plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        return Inertia::render('Admin/Plans/Edit', [
            'plan' => $plan,
            'available_modules' => Plan::AVAILABLE_MODULES,
        ]);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:self_hosted,managed_hosting',
            'price_inr' => 'required|numeric|min:0',
            'renewal_price_inr' => 'nullable|numeric|min:0',
            'price_usd' => 'required|numeric|min:0',
            'renewal_price_usd' => 'nullable|numeric|min:0',
            'billing_period' => 'required|string|max:255',
            'max_domains' => 'required|integer|min:1',
            'features' => 'required|array',
            'features.*' => 'required|string|max:255',
            'modules' => 'nullable|array',
            'modules.*' => 'string',
            'is_active' => 'required|boolean',
            'is_popular' => 'required|boolean',
            'cta_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }

    public function toggleActive(Plan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);

        return back()->with('success', "Plan '{$plan->name}' status changed to " . ($plan->is_active ? 'Active' : 'Inactive') . '.');
    }
}
