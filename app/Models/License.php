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
}
