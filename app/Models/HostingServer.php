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
        'api_secret',
        'status',
        'location',
        'specs',
        'notes',
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

    public function accounts()
    {
        return $this->hasMany(HostingAccount::class, 'server_id');
    }
}
