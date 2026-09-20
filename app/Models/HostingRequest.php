<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'domain',
        'plan_requested',
        'requirements',
        'notes',
        'status',
        'admin_notes',
    ];

    protected $appends = [
        'notes',
    ];

    public function getNotesAttribute(): ?string
    {
        return $this->attributes['requirements'] ?? null;
    }

    public function setNotesAttribute($value): void
    {
        $this->attributes['requirements'] = $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
