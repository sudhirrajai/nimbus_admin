<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    /**
     * Canonical list of modular features controllable on Nimbus.
     */
    public const AVAILABLE_MODULES = [
        'wordpress'     => 'WordPress Toolkit & 1-Click Installer',
        'security'      => 'Security, Firewall & Fail2ban',
        'databases'     => 'Database Manager & phpMyAdmin',
        'cron'          => 'Scheduled Cron Jobs',
        'supervisor'    => 'Supervisor Worker Daemons',
        'file_manager'  => 'Advanced File Manager & Editor',
        'terminal'      => 'Web Terminal & Shell Console',
        'backups'       => 'Automated & Remote Cloud Backups',
        'git_deploy'    => 'Git Auto-Deployments & Webhooks',
        'ssl'           => 'Automated SSL & Wildcard Certificates',
        'emails'        => 'Mail Server & Roundcube Webmail',
        'monitoring'    => 'Resource History & Performance Analytics',
    ];

    protected $fillable = [
        'name',
        'slug',
        'type',
        'price_inr',
        'renewal_price_inr',
        'price_usd',
        'renewal_price_usd',
        'billing_period',
        'max_domains',
        'features',
        'modules',
        'is_active',
        'is_popular',
        'cta_text',
        'description',
    ];

    protected $casts = [
        'features' => 'array',
        'modules' => 'array',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
    ];

    public function scopeSelfHosted($query)
    {
        return $query->where(function ($q) {
            $q->where('type', 'self_hosted')
              ->orWhereNull('type');
        });
    }

    public function scopeManagedHosting($query)
    {
        return $query->where('type', 'managed_hosting');
    }

    /**
     * Check if a module is enabled by default in this plan.
     */
    public function hasModule(string $module): bool
    {
        $mods = $this->modules ?? [];
        return in_array($module, $mods, true);
    }
}
