<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BugReport;
use Illuminate\Support\Facades\Validator;

class BugReportController extends Controller
{
    /**
     * Store a new bug report.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'nullable|string',
            'domain'      => 'nullable|string',
            'admin_name'  => 'nullable|string',
            'admin_email' => 'nullable|string',
            'message'     => 'required|string',
            'screenshot'  => 'nullable|image|max:10240', // 10MB max
            'images'      => 'nullable|array',
            'images.*'    => 'image|max:10240',
            'ip_address'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid parameters.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $screenshotPath = null;
        if ($request->hasFile('screenshot')) {
            $screenshotPath = $request->file('screenshot')->store('bug-reports', 'public');
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('bug-reports', 'public');
            }
        }

        $report = BugReport::create([
            'license_key'     => $request->license_key,
            'domain'          => $request->domain,
            'admin_name'      => $request->admin_name,
            'admin_email'     => $request->admin_email,
            'message'         => $request->message,
            'screenshot_path' => $screenshotPath,
            'images'          => $imagePaths,
            'ip_address'      => $request->ip_address ?? $request->ip(),
            'status'          => 'pending',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Bug report submitted successfully.',
            'report'  => $report
        ], 201);
    }
}
