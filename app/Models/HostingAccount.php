<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostingAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'server_id',
        'hosting_server_id',
        'primary_domain',
        'domain',
        'username',
        'package_name',
        'plan_name',
        'billing_cycle',
        'initial_price',
        'renewal_price',
        'currency',
        'renews_at',
        'auto_invoice',
        'renewal_invoice_days',
        'status',
        'notes',
    ];

    protected $casts = [
        'initial_price' => 'decimal:2',
        'renewal_price' => 'decimal:2',
        'renews_at' => 'datetime',
        'auto_invoice' => 'boolean',
        'renewal_invoice_days' => 'integer',
    ];

    protected $appends = [
        'domain',
        'plan_name',
        'hosting_server_id',
        'human_billing_cycle',
    ];

    protected static function booted(): void
    {
        static::creating(function ($account) {
            if (empty($account->uuid)) {
                $account->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('uuid', $value)->orWhere('id', $value)->firstOrFail();
    }

    public function getDomainAttribute(): ?string
    {
        return $this->attributes['primary_domain'] ?? null;
    }

    public function setDomainAttribute($value): void
    {
        $this->attributes['primary_domain'] = $value;
    }

    public function getPlanNameAttribute(): ?string
    {
        return $this->attributes['package_name'] ?? null;
    }

    public function setPlanNameAttribute($value): void
    {
        $this->attributes['package_name'] = $value;
    }

    public function getHostingServerIdAttribute(): ?int
    {
        return $this->attributes['server_id'] ?? null;
    }

    public function setHostingServerIdAttribute($value): void
    {
        $this->attributes['server_id'] = $value;
    }

    public function getHumanBillingCycleAttribute(): string
    {
        $cycles = [
            'monthly' => '1 Month',
            'quarterly' => '3 Months',
            'semi_annual' => '6 Months',
            'yearly' => '1 Year (Annual)',
            'biennial' => '2 Years',
            'triennial' => '3 Years',
        ];

        return $cycles[$this->billing_cycle ?? 'yearly'] ?? ucfirst($this->billing_cycle ?? 'yearly');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function server()
    {
        return $this->belongsTo(HostingServer::class, 'server_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function latestInvoice()
    {
        return $this->hasOne(Invoice::class)->latestOfMany();
    }

    /**
     * Compute next cycle expiration based on a starting date and billing cycle.
     */
    public function computeNextPeriodEnd(?Carbon $startDate = null): Carbon
    {
        $start = $startDate ? $startDate->copy() : ($this->renews_at ? $this->renews_at->copy() : now());

        return match ($this->billing_cycle) {
            'monthly' => $start->addMonth(),
            'quarterly' => $start->addMonths(3),
            'semi_annual' => $start->addMonths(6),
            'biennial' => $start->addYears(2),
            'triennial' => $start->addYears(3),
            default => $start->addYear(),
        };
    }

    /**
     * Determine if this account is due for an automated renewal invoice.
     */
    public function isDueForRenewalInvoice(): bool
    {
        if (!$this->auto_invoice || !$this->renews_at || $this->status !== 'active') {
            return false;
        }

        $leadDays = $this->renewal_invoice_days > 0 ? $this->renewal_invoice_days : 14;
        $threshold = now()->addDays($leadDays);

        // Due if renews_at is on or before the threshold
        if ($this->renews_at->greaterThan($threshold)) {
            return false;
        }

        // Check if an upcoming renewal invoice has already been issued within the last 45 days
        $recentInvoiceExists = $this->invoices()
            ->where(function ($q) {
                $q->where('status', 'pending')
                  ->orWhere(function ($sq) {
                      $sq->where('status', 'paid')
                         ->where('created_at', '>=', now()->subDays(45));
                  });
            })
            ->where(function ($q) {
                $q->where('description', 'LIKE', '%Renewal%')
                  ->orWhere('due_date', '>=', now()->subDays(15));
            })
            ->exists();

        return !$recentInvoiceExists;
    }

    /**
     * Generate a renewal invoice for this hosting account.
     */
    public function generateRenewalInvoice(?float $customAmount = null, ?string $paymentStatus = 'pending', ?string $paymentMethod = 'Pending Renewal'): Invoice
    {
        $amount = $customAmount !== null ? $customAmount : (float) ($this->renewal_price ?? $this->initial_price ?? 0.00);
        $periodStart = $this->renews_at ? $this->renews_at->copy() : now();
        $periodEnd = $this->computeNextPeriodEnd($periodStart);

        $invoice = Invoice::create([
            'user_id' => $this->user_id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'type' => 'hosting_plan',
            'plan_name' => $this->plan_name ?? $this->package_name,
            'description' => "Managed Cloud Hosting Renewal - {$this->domain} (Term: {$periodStart->format('M d, Y')} to {$periodEnd->format('M d, Y')})",
            'amount' => $amount,
            'currency' => strtoupper($this->currency ?? 'INR'),
            'status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'due_date' => $periodStart,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'paid_at' => $paymentStatus === 'paid' ? now() : null,
            'hosting_account_id' => $this->id,
            'billing_details' => [
                'customer_name' => $this->user?->name ?? 'Customer',
                'customer_email' => $this->user?->email ?? '',
                'domain' => $this->domain,
                'server' => $this->server?->name,
                'billing_cycle' => $this->billing_cycle ?? 'yearly',
                'is_renewal' => true,
                'renewal_price' => $this->renewal_price,
            ],
        ]);

        return $invoice;
    }
}
