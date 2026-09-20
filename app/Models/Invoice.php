<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'invoice_number',
        'type',
        'plan_name',
        'description',
        'amount',
        'currency',
        'status',
        'payment_method',
        'payment_id',
        'paid_at',
        'due_date',
        'period_start',
        'period_end',
        'billing_details',
        'license_id',
        'hosting_account_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'due_date' => 'datetime',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'billing_details' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($invoice) {
            if (empty($invoice->uuid)) {
                $invoice->uuid = (string) Str::uuid();
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function license()
    {
        return $this->belongsTo(License::class);
    }

    public function hostingAccount()
    {
        return $this->belongsTo(HostingAccount::class);
    }

    /**
     * Generate sequential/unique invoice number: INV-YYYYMM-XXXX
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('Ym') . '-';
        $latest = self::where('invoice_number', 'LIKE', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->invoice_number, strlen($prefix));
            $nextNumber = str_pad((string)($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }
}
