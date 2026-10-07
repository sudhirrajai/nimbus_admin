<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NimbusFeature;
use App\Models\NimbusFaq;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminNimbusController extends Controller
{
    public const AVAILABLE_ICONS = [
        ['key' => 'cpu', 'label' => 'CPU / Processor', 'category' => 'Hardware'],
        ['key' => 'terminal', 'label' => 'Terminal / Shell Console', 'category' => 'DevOps'],
        ['key' => 'shield', 'label' => 'Shield / Security & Firewall', 'category' => 'Security'],
        ['key' => 'zap', 'label' => 'Zap / Speed & Performance', 'category' => 'Performance'],
        ['key' => 'database', 'label' => 'Database / MySQL & Redis', 'category' => 'Database'],
        ['key' => 'workflow', 'label' => 'Workflow / CI/CD Automation', 'category' => 'DevOps'],
        ['key' => 'server', 'label' => 'Server / Dedicated Node', 'category' => 'Infrastructure'],
        ['key' => 'cloud', 'label' => 'Cloud / Virtual Instance', 'category' => 'Infrastructure'],
        ['key' => 'lock', 'label' => 'Lock / SSL & Encryption', 'category' => 'Security'],
        ['key' => 'code', 'label' => 'Code / Developer API', 'category' => 'DevOps'],
        ['key' => 'hard-drive', 'label' => 'Hard Drive / NVMe Storage', 'category' => 'Hardware'],
        ['key' => 'activity', 'label' => 'Activity / Uptime & Monitoring', 'category' => 'Monitoring'],
        ['key' => 'layers', 'label' => 'Layers / Software Stacks', 'category' => 'DevOps'],
        ['key' => 'mail', 'label' => 'Mail / Roundcube & SMTP', 'category' => 'Email'],
    ];

    public function index()
    {
        return Inertia::render('Admin/Nimbus/Index', [
            'features' => NimbusFeature::orderBy('sort_order')->orderBy('id')->get(),
            'faqs' => NimbusFaq::orderBy('sort_order')->orderBy('id')->get(),
            'available_icons' => self::AVAILABLE_ICONS,
        ]);
    }

    // Feature methods
    public function storeFeature(Request $request)
    {
        $validated = $request->validate([
            'icon' => 'required|string|max:50',
            'tag' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'copy' => 'required|string|max:2000',
            'sort_order' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? (NimbusFeature::max('sort_order') + 1);

        NimbusFeature::create($validated);

        return redirect()->route('admin.nimbus.index')
            ->with('success', 'Architecture feature added successfully.');
    }

    public function updateFeature(Request $request, NimbusFeature $feature)
    {
        $validated = $request->validate([
            'icon' => 'required|string|max:50',
            'tag' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'copy' => 'required|string|max:2000',
            'sort_order' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $feature->update($validated);

        return redirect()->route('admin.nimbus.index')
            ->with('success', 'Architecture feature updated successfully.');
    }

    public function destroyFeature(NimbusFeature $feature)
    {
        $feature->delete();

        return redirect()->route('admin.nimbus.index')
            ->with('success', 'Architecture feature deleted successfully.');
    }

    public function toggleActiveFeature(NimbusFeature $feature)
    {
        $feature->update(['is_active' => !$feature->is_active]);

        return back()->with('success', "Feature '{$feature->title}' is now " . ($feature->is_active ? 'active' : 'hidden') . '.');
    }

    // FAQ methods
    public function storeFaq(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:4000',
            'sort_order' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? (NimbusFaq::max('sort_order') + 1);

        NimbusFaq::create($validated);

        return redirect()->route('admin.nimbus.index')
            ->with('success', 'FAQ added successfully.');
    }

    public function updateFaq(Request $request, NimbusFaq $faq)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:4000',
            'sort_order' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $faq->update($validated);

        return redirect()->route('admin.nimbus.index')
            ->with('success', 'FAQ updated successfully.');
    }

    public function destroyFaq(NimbusFaq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.nimbus.index')
            ->with('success', 'FAQ deleted successfully.');
    }

    public function toggleActiveFaq(NimbusFaq $faq)
    {
        $faq->update(['is_active' => !$faq->is_active]);

        return back()->with('success', "FAQ status changed to " . ($faq->is_active ? 'active' : 'hidden') . '.');
    }
}
