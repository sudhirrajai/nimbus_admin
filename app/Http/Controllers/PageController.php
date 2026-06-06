<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Inertia\Inertia;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        
        // Fetch list of all active pages for the side menu navigation
        $otherPages = Page::select('slug', 'title')->get();

        return Inertia::render('PageShow', [
            'page' => $page,
            'otherPages' => $otherPages
        ]);
    }
}
