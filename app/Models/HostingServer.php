<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HostingServer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hostname',
        'ip_address',
        'nimbus_url',
        'panel_url',
        'api_secret',
        'status',
        'is_active',
        'location',
        'specs',
        'notes',
    ];

    protected $appends = [
        'panel_url',
        'is_active',
    ];

    protected $casts = [
        'specs' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($server) {
            if (empty($server->api_secret)) {
                $server->api_secret = Str::random(48);
            }
        });
    }

    public function getPanelUrlAttribute(): ?string
    {
        return $this->attributes['nimbus_url'] ?? null;
    }

    public function setPanelUrlAttribute($value): void
    {
        $this->attributes['nimbus_url'] = $value;
    }

    public function getIsActiveAttribute(): bool
    {
        return ($this->attributes['status'] ?? 'active') === 'active';
    }

    public function setIsActiveAttribute($value): void
    {
        $this->attributes['status'] = $value ? 'active' : 'maintenance';
    }

    public function accounts()
    {
        return $this->hasMany(HostingAccount::class, 'server_id');
    }
}
