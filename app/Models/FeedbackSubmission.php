<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FeedbackSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'feedback_form_id',
        'feedback_invitation_id',
        'user_id',
        'client_name',
        'client_email',
        'client_company',
        'client_role',
        'rating',
        'feedback',
        'suggestions',
        'answers',
        'testimonial_id',
        'is_testimonial',
        'status',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'answers' => 'array',
        'rating' => 'integer',
        'is_testimonial' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($submission) {
            if (empty($submission->uuid)) {
                $submission->uuid = (string) Str::uuid();
            }
        });
    }

    public function form()
    {
        return $this->belongsTo(FeedbackForm::class, 'feedback_form_id');
    }

    public function invitation()
    {
        return $this->belongsTo(FeedbackInvitation::class, 'feedback_invitation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function testimonial()
    {
        return $this->belongsTo(Testimonial::class);
    }
}
