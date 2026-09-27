<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'subject',
        'category',
        'priority',
        'status',
        'last_reply_at',
        'last_reply_by',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'last_reply_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected $appends = [
        'is_resolved',
        'is_closed',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'NB-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
            }
            if (empty($ticket->status)) {
                $ticket->status = 'open';
            }
            if (empty($ticket->last_reply_at)) {
                $ticket->last_reply_at = now();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lastReplier()
    {
        return $this->belongsTo(User::class, 'last_reply_by');
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at', 'asc');
    }

    public function publicMessages()
    {
        return $this->hasMany(TicketMessage::class)
            ->where('is_internal', false)
            ->orderBy('created_at', 'asc');
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'answered']);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function getIsResolvedAttribute(): bool
    {
        return $this->status === 'resolved';
    }

    public function getIsClosedAttribute(): bool
    {
        return $this->status === 'closed';
    }
}
