<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FeedbackInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'feedback_form_id',
        'user_id',
        'recipient_email',
        'recipient_name',
        'token',
        'status',
        'sent_at',
        'completed_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $appends = [
        'invite_url',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invitation) {
            if (empty($invitation->uuid)) {
                $invitation->uuid = (string) Str::uuid();
            }
            if (empty($invitation->token)) {
                $invitation->token = 'inv_' . Str::random(40);
            }
            if (empty($invitation->sent_at)) {
                $invitation->sent_at = now();
            }
        });
    }

    public function form()
    {
        return $this->belongsTo(FeedbackForm::class, 'feedback_form_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function submission()
    {
        return $this->hasOne(FeedbackSubmission::class, 'feedback_invitation_id');
    }

    public function getInviteUrlAttribute(): string
    {
        return route('feedback.show', ['identifier' => $this->token]);
    }
}
