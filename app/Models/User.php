<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

#[Fillable(['uuid', 'name', 'email', 'phone', 'company_name', 'address', 'city', 'state', 'postal_code', 'country', 'notes', 'password', 'is_admin', 'is_active', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $appends = ['customer_code'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail());
    }

    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
            if (!isset($user->is_active)) {
                $user->is_active = true;
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

    public function getCustomerCodeAttribute(): string
    {
        if (!empty($this->uuid)) {
            return 'CUST-' . strtoupper(substr(str_replace('-', '', $this->uuid), 0, 8));
        }
        return 'CUST-' . str_pad((string)$this->id, 6, '0', STR_PAD_LEFT);
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function hostingAccounts()
    {
        return $this->hasMany(HostingAccount::class);
    }

    public function hostingRequests()
    {
        return $this->hasMany(HostingRequest::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
