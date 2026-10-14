<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseActivation extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_id',
        'machine_id',
        'server_ip',
        'domain',
        'admin_name',
        'admin_email',
        'last_heartbeat_at',
    ];

    protected $casts = [
        'last_heartbeat_at' => 'datetime',
    ];

    public function license()
    {
        return $this->belongsTo(License::class);
    }
}
