<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminTestimonialController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Testimonials/Index', [
            'testimonials' => Testimonial::orderBy('sort_order')->orderBy('id', 'desc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'quote' => 'required|string|max:2000',
            'rating' => 'required|integer|min:1|max:5',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Testimonial::create($validated);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial added successfully.');
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'quote' => 'required|string|max:2000',
            'rating' => 'required|integer|min:1|max:5',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $testimonial->update($validated);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial deleted successfully.');
    }

    public function toggleActive(Testimonial $testimonial)
    {
        $testimonial->update(['is_active' => !$testimonial->is_active]);

        return back()->with('success', "Testimonial from '{$testimonial->name}' is now " . ($testimonial->is_active ? 'Active' : 'Hidden') . '.');
    }
}
