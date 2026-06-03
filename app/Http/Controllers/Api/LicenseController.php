<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\License;
use Illuminate\Support\Facades\Validator;

class LicenseController extends Controller
{
    /**
     * Verify a license key
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string',
            'server_ip' => 'required|string',
            'machine_id' => 'required|string',
            'domain' => 'nullable|string',
            'admin_name' => 'nullable|string',
            'admin_email' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid validation parameters.',
                'errors' => $validator->errors()
            ], 422);
        }

        $license = License::where('license_key', $request->license_key)->first();

        if (!$license) {
            return response()->json([
                'status' => false,
                'message' => 'License key not found.'
            ], 404);
        }

        if ($license->status !== 'active') {
            return response()->json([
                'status' => false,
                'message' => 'License is ' . $license->status . '.'
            ], 403);
        }

        if ($license->expires_at && $license->expires_at->isPast()) {
            $license->update(['status' => 'expired']);
            return response()->json([
                'status' => false,
                'message' => 'License has expired.'
            ], 403);
        }

        // Lock to IP/Machine if not already locked
        if (!$license->server_ip) {
            $license->server_ip = $request->server_ip;
        }

        if (!$license->machine_id) {
            $license->machine_id = $request->machine_id;
        }

        // Validate Lock
        if ($license->server_ip !== $request->server_ip || $license->machine_id !== $request->machine_id) {
             return response()->json([
                'status' => false,
                'message' => 'License is locked to another server.'
            ], 403);
        }

        $license->update([
            'last_checked_at' => now(),
            'domain' => $request->domain ?? $license->domain,
            'admin_name' => $request->admin_name ?? $license->admin_name,
            'admin_email' => $request->admin_email ?? $license->admin_email,
        ]);

        return response()->json([
            'status' => true,
            'plan' => $license->plan,
            'expires_at' => $license->expires_at ? $license->expires_at->toDateTimeString() : 'Never',
            'message' => 'License is valid.'
        ]);
    }
}
