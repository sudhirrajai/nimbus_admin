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
        'monthly_price_inr',
        'renewal_price_inr',
        'renewal_monthly_price_inr',
        'price_usd',
        'monthly_price_usd',
        'renewal_price_usd',
        'renewal_monthly_price_usd',
        'price_2year_inr',
        'price_2year_usd',
        'renewal_price_2year_inr',
        'renewal_price_2year_usd',
        'price_3year_inr',
        'price_3year_usd',
        'renewal_price_3year_inr',
        'renewal_price_3year_usd',
        'billing_period',
        'max_domains',
        'max_servers',
        'features',
        'modules',
        'is_active',
        'is_popular',
        'cta_text',
        'description',
    ];

    /**
     * Get price for a specific billing cycle and currency.
     */
    public function getPriceForCycle(string $cycle, string $currency = 'INR'): float
    {
        $currency = strtoupper($currency);
        $isUsd = ($currency === 'USD');

        return match ($cycle) {
            'monthly' => (float) ($isUsd ? ($this->monthly_price_usd ?: $this->price_usd) : ($this->monthly_price_inr ?: $this->price_inr)),
            '2years', '2year', '2_years' => (float) ($isUsd ? ($this->price_2year_usd ?: $this->price_usd * 2) : ($this->price_2year_inr ?: $this->price_inr * 2)),
            '3years', '3year', '3_years' => (float) ($isUsd ? ($this->price_3year_usd ?: $this->price_usd * 3) : ($this->price_3year_inr ?: $this->price_inr * 3)),
            default => (float) ($isUsd ? $this->price_usd : $this->price_inr),
        };
    }

    /**
     * Get renewal price for a specific billing cycle and currency.
     */
    public function getRenewalPriceForCycle(string $cycle, string $currency = 'INR'): float
    {
        $currency = strtoupper($currency);
        $isUsd = ($currency === 'USD');

        return match ($cycle) {
            'monthly' => (float) ($isUsd ? ($this->renewal_monthly_price_usd ?: $this->monthly_price_usd ?: $this->renewal_price_usd) : ($this->renewal_monthly_price_inr ?: $this->monthly_price_inr ?: $this->renewal_price_inr)),
            '2years', '2year', '2_years' => (float) ($isUsd ? ($this->renewal_price_2year_usd ?: $this->price_2year_usd ?: $this->renewal_price_usd * 2) : ($this->renewal_price_2year_inr ?: $this->price_2year_inr ?: $this->renewal_price_inr * 2)),
            '3years', '3year', '3_years' => (float) ($isUsd ? ($this->renewal_price_3year_usd ?: $this->price_3year_usd ?: $this->renewal_price_usd * 3) : ($this->renewal_price_3year_inr ?: $this->price_3year_inr ?: $this->renewal_price_inr * 3)),
            default => (float) ($isUsd ? ($this->renewal_price_usd ?: $this->price_usd) : ($this->renewal_price_inr ?: $this->price_inr)),
        };
    }

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
