<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostingAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'server_id',
        'hosting_server_id',
        'primary_domain',
        'domain',
        'username',
        'package_name',
        'plan_name',
        'status',
        'notes',
    ];

    protected $appends = [
        'domain',
        'plan_name',
        'hosting_server_id',
    ];

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function server()
    {
        return $this->belongsTo(HostingServer::class, 'server_id');
    }
}
