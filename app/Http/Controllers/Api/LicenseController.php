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
    private function createSignedToken(License $license, string $machineId, string $serverIp): string
    {
        $modules = $license->getEffectiveModules();

        $payload = json_encode([
            'license_key' => $license->license_key,
            'machine_id'  => $machineId,
            'server_ip'   => $serverIp,
            'plan'        => $license->plan,
            'modules'     => $modules,
            'feature_overrides' => $license->feature_overrides ?? (object)[],
            'expires_at'  => $license->expires_at ? $license->expires_at->toIso8601String() : null,
            'max_domains' => $this->getMaxDomains($license->plan),
            'issued_at'   => now()->toIso8601String(),
            'valid_until' => now()->addHours(24)->toIso8601String(),
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
     * Supports dynamic multi-server activations up to the plan or admin limit.
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

        // Multi-server activation management
        $activation = $license->activations()->where('machine_id', $request->machine_id)->first();

        if ($activation) {
            // Already registered machine: update details and refresh heartbeat
            $activation->update([
                'server_ip'         => $request->server_ip,
                'domain'            => $request->domain ?? $activation->domain,
                'admin_name'        => $request->admin_name ?? $activation->admin_name,
                'admin_email'       => $request->admin_email ?? $activation->admin_email,
                'last_heartbeat_at' => now(),
            ]);
        } else {
            // New machine registration: enforce max server activation limit
            $maxServers = $license->getMaxServers();
            $currentCount = $license->activations()->count();

            if ($currentCount >= $maxServers) {
                return response()->json([
                    'status'  => false,
                    'message' => "Activation limit reached ({$currentCount}/{$maxServers} servers). Please disconnect an existing server or upgrade.",
                ], 403);
            }

            $activation = $license->activations()->create([
                'machine_id'        => $request->machine_id,
                'server_ip'         => $request->server_ip,
                'domain'            => $request->domain,
                'admin_name'        => $request->admin_name,
                'admin_email'       => $request->admin_email,
                'last_heartbeat_at' => now(),
            ]);
        }

        // Update legacy fields for fast indexing / backwards compatibility
        $license->update([
            'last_checked_at' => now(),
            'server_ip'       => $request->server_ip,
            'machine_id'      => $request->machine_id,
            'domain'          => $request->domain ?? $license->domain,
            'admin_name'      => $request->admin_name ?? $license->admin_name,
            'admin_email'     => $request->admin_email ?? $license->admin_email,
        ]);

        // Generate signed token specifically for this machine
        $signedToken = $this->createSignedToken($license, $request->machine_id, $request->server_ip);
        $modules = $license->getEffectiveModules();

        return response()->json([
            'status'       => true,
            'plan'         => $license->plan,
            'modules'      => $modules,
            'feature_overrides' => $license->feature_overrides ?? (object)[],
            'expires_at'   => $license->expires_at ? $license->expires_at->toDateTimeString() : 'Never',
            'message'      => 'License is valid.',
            'signed_token' => $signedToken,
            'max_domains'  => $this->getMaxDomains($license->plan),
            'max_servers'  => $license->getMaxServers(),
            'active_servers' => $license->activations()->count(),
            'status_changed_at' => $license->status_changed_at?->toIso8601String(),
        ]);
    }

    /**
     * Heartbeat ping — lightweight check to confirm license is still active
     * on this specific registered machine.
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

        // Validate that this machine has an active activation
        $activation = $license->activations()->where('machine_id', $request->machine_id)->first();

        if (!$activation) {
            Log::warning("Heartbeat received for unregistered or disconnected machine: key={$request->license_key}, mid={$request->machine_id}, ip={$request->server_ip}");
            return response()->json([
                'status'  => false,
                'message' => 'Server activation not found or has been disconnected from dashboard.',
            ], 403);
        }

        $activation->update([
            'server_ip'         => $request->server_ip,
            'last_heartbeat_at' => now(),
        ]);

        $license->update([
            'last_heartbeat_at' => now(),
            'last_checked_at'   => now(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'OK',
            'license_status' => $license->status,
            'modules' => $license->getEffectiveModules(),
            'signed_token' => $this->createSignedToken($license, $request->machine_id, $request->server_ip),
            'status_changed_at' => $license->status_changed_at?->toIso8601String(),
        ]);
    }

    /**
     * Deactivate / Disconnect a license from a specific machine
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

        // Look for the machine activation
        $activation = $license->activations()->where('machine_id', $request->machine_id)->first();

        if (!$activation) {
            return response()->json([
                'status'  => false,
                'message' => 'License is not active on this machine.'
            ], 404);
        }

        $activation->delete();

        // If no more activations, clear the primary cached machine lock
        if ($license->activations()->count() === 0) {
            $license->update([
                'server_ip'    => null,
                'machine_id'   => null,
                'domain'       => null,
                'status_changed_at' => now(),
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'License deactivated successfully from this machine.'
        ]);
    }
}
