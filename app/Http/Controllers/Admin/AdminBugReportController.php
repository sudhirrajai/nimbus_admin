<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BugReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminBugReportController extends Controller
{
    /**
     * Display a listing of bug reports.
     */
    public function index()
    {
        $reports = BugReport::orderBy('created_at', 'desc')->get();

        return Inertia::render('Admin/BugReports', [
            'reports' => $reports
        ]);
    }

    /**
     * Update the status of a bug report.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,resolved,closed'
        ]);

        $report = BugReport::findOrFail($id);
        $report->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Bug report status updated successfully.');
    }

    /**
     * Delete a bug report and its stored images.
     */
    public function destroy($id)
    {
        $report = BugReport::findOrFail($id);

        // Clean up screenshot file
        if ($report->screenshot_path) {
            Storage::disk('public')->delete($report->screenshot_path);
        }

        // Clean up other image files
        if (is_array($report->images)) {
            foreach ($report->images as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $report->delete();

        return back()->with('success', 'Bug report deleted successfully.');
    }
}
