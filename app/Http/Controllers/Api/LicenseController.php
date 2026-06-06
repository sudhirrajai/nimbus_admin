<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\License;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class LicenseController extends Controller
{
    /**
     * Load the RSA private key for signing license tokens.
     */
    private function getPrivateKey()
    {
        $path = storage_path('app/license_keys/private.pem');
        if (!file_exists($path)) {
            return null;
        }
        return openssl_pkey_get_private(file_get_contents($path));
    }

    /**
     * Create a signed license token containing license metadata.
     * The token is a base64url-encoded JSON payload + '.' + base64url-encoded RSA signature.
     */
    private function createSignedToken(License $license): string
    {
        $payload = json_encode([
            'license_key' => $license->license_key,
            'machine_id'  => $license->machine_id,
            'server_ip'   => $license->server_ip,
            'plan'        => $license->plan,
            'expires_at'  => $license->expires_at ? $license->expires_at->toIso8601String() : null,
            'max_domains' => $this->getMaxDomains($license->plan),
            'issued_at'   => now()->toIso8601String(),
            'valid_until'  => now()->addHours(24)->toIso8601String(),
        ]);

        $privateKey = $this->getPrivateKey();
        if (!$privateKey) {
            Log::error('License signing failed: private key not found');
            return '';
        }

        openssl_sign($payload, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        // Format: base64url(payload).base64url(signature)
        return $this->base64urlEncode($payload) . '.' . $this->base64urlEncode($signature);
    }

    /**
     * Get max domains allowed for a given plan.
     */
    private function getMaxDomains(string $plan): int
    {
        $dbPlan = \App\Models\Plan::where('slug', $plan)->first();
        if ($dbPlan) {
            return $dbPlan->max_domains;
        }

        return match ($plan) {
            'free'       => 3,
            'pro'        => 50,
            'enterprise' => 9999,
            default      => 3,
        };
    }

    private function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Verify a license key and return a cryptographically signed token.
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string',
            'server_ip'   => 'required|string',
            'machine_id'  => 'required|string',
            'domain'      => 'nullable|string',
            'admin_name'  => 'nullable|string',
            'admin_email' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid validation parameters.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $license = License::where('license_key', $request->license_key)->first();

        if (!$license) {
            return response()->json([
                'status'  => false,
                'message' => 'License key not found.'
            ], 404);
        }

        if ($license->status !== 'active') {
            return response()->json([
                'status'  => false,
                'message' => 'License is ' . $license->status . '.'
            ], 403);
        }

        if ($license->expires_at && $license->expires_at->isPast()) {
            $license->update(['status' => 'expired']);
            return response()->json([
                'status'  => false,
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

        // Validate Lock (matches either the client's reported SERVER_ADDR or their public IP seen by the server)
        $clientIp = $request->ip();
        $ipMatches = ($license->server_ip === $request->server_ip) || ($license->server_ip === $clientIp);

        if (!$ipMatches || $license->machine_id !== $request->machine_id) {
            return response()->json([
                'status'  => false,
                'message' => 'License is locked to another server.'
            ], 403);
        }

        $license->update([
            'last_checked_at' => now(),
            'domain'          => $request->domain ?? $license->domain,
            'admin_name'      => $request->admin_name ?? $license->admin_name,
            'admin_email'     => $request->admin_email ?? $license->admin_email,
        ]);

        // Generate signed token
        $signedToken = $this->createSignedToken($license);

        return response()->json([
            'status'       => true,
            'plan'         => $license->plan,
            'expires_at'   => $license->expires_at ? $license->expires_at->toDateTimeString() : 'Never',
            'message'      => 'License is valid.',
            'signed_token' => $signedToken,
            'max_domains'  => $this->getMaxDomains($license->plan),
            'status_changed_at' => $license->status_changed_at?->toIso8601String(),
        ]);
    }

    /**
     * Heartbeat ping — lightweight check to confirm license is still active
     * and the installation hasn't migrated to a different server.
     */
    public function heartbeat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string',
            'machine_id'  => 'required|string',
            'server_ip'   => 'required|string',
            'version'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid parameters.',
            ], 422);
        }

        $license = License::where('license_key', $request->license_key)->first();

        if (!$license) {
            return response()->json(['status' => false, 'message' => 'License key not found.'], 404);
        }

        if ($license->status !== 'active') {
            return response()->json(['status' => false, 'message' => 'License is ' . $license->status . '.'], 403);
        }

        // Validate machine lock
        $clientIp = $request->ip();
        $ipMatches = ($license->server_ip === $request->server_ip) || ($license->server_ip === $clientIp);

        if (!$ipMatches || $license->machine_id !== $request->machine_id) {
            // IP or machine mismatch — potential license sharing / cloning
            Log::warning("License heartbeat mismatch for {$request->license_key}: " .
                "expected IP={$license->server_ip}, got={$request->server_ip} (client={$clientIp}), " .
                "expected MID={$license->machine_id}, got={$request->machine_id}");

            $license->update(['status' => 'suspended']);

            return response()->json([
                'status'  => false,
                'message' => 'License suspended due to server mismatch.',
            ], 403);
        }

        $license->update([
            'last_heartbeat_at' => now(),
            'last_checked_at'   => now(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'OK',
            'license_status' => $license->status,
            'status_changed_at' => $license->status_changed_at?->toIso8601String(),
        ]);
    }

    /**
     * Deactivate / Disconnect a license from a machine
     */
    public function deactivate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string',
            'machine_id'  => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid validation parameters.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $license = License::where('license_key', $request->license_key)->first();

        if (!$license) {
            return response()->json([
                'status'  => false,
                'message' => 'License key not found.'
            ], 404);
        }

        // Verify machine lock matches
        if ($license->machine_id !== $request->machine_id) {
            return response()->json([
                'status'  => false,
                'message' => 'License is not active on this machine.'
            ], 403);
        }

        // Disconnect
        $license->update([
            'server_ip'    => null,
            'machine_id'   => null,
            'domain'       => null,
            'admin_name'   => null,
            'admin_email'  => null,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'License deactivated successfully from this machine.'
        ]);
    }
}
