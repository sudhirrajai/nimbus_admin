<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_key',
        'server_ip',
        'machine_id',
        'domain',
        'admin_name',
        'admin_email',
        'plan',
        'feature_overrides',
        'status',
        'expires_at',
        'last_checked_at',
        'razorpay_payment_id',
        'razorpay_order_id',
        'last_heartbeat_at',
        'status_changed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'last_heartbeat_at' => 'datetime',
        'status_changed_at' => 'datetime',
        'feature_overrides' => 'array',
    ];

    /**
     * Generate a unique license key
     */
    public static function generateKey($plan = 'free')
    {
        $prefix = strtoupper(substr($plan, 0, 3));
        do {
            $key = $prefix . '-' . strtoupper(Str::random(16));
        } while (self::where('license_key', $key)->exists());

        return $key;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function planDetails()
    {
        return $this->belongsTo(Plan::class, 'plan', 'slug');
    }

    /**
     * Compute effective active modules for this license:
     * - Base modules from Plan
     * - Overridden modules (e.g. promotional grants or temporary trials)
     */
    public function getEffectiveModules(): array
    {
        $plan = $this->planDetails;
        $baseModules = $plan && is_array($plan->modules) ? $plan->modules : $this->getDefaultPlanModules($this->plan);
        $overrides = is_array($this->feature_overrides) ? $this->feature_overrides : [];

        $effective = array_fill_keys($baseModules, true);

        foreach ($overrides as $module => $rule) {
            if (!is_array($rule)) {
                $enabled = (bool) $rule;
                $expiresAt = null;
            } else {
                $enabled = (bool) ($rule['enabled'] ?? false);
                $expiresAt = isset($rule['expires_at']) ? strtotime($rule['expires_at']) : null;
            }

            // Check if temporary grant has expired
            if ($enabled && $expiresAt && $expiresAt < time()) {
                $enabled = false;
            }

            if ($enabled) {
                $effective[$module] = true;
            } else {
                unset($effective[$module]);
            }
        }

        return array_values(array_keys($effective));
    }

    /**
     * Fallback plan module mappings if Plan record does not define modules yet.
     */
    private function getDefaultPlanModules(string $plan): array
    {
        $core = ['file_manager', 'databases', 'ssl', 'monitoring'];
        $standard = array_merge($core, ['cron', 'supervisor', 'wordpress', 'git_deploy']);
        $all = array_keys(Plan::AVAILABLE_MODULES);

        return match (strtolower($plan)) {
            'free'       => $core,
            'pro'        => $standard,
            'pro+', 'pro_plus' => array_merge($standard, ['security', 'emails']),
            'enterprise' => $all,
            default      => $core,
        };
    }
}
