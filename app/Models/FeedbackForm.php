<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FeedbackForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'description',
        'category',
        'questions',
        'is_active',
        'allow_anonymous',
        'collect_company',
        'collect_role',
        'success_title',
        'success_message',
        'created_by',
    ];

    protected $casts = [
        'questions' => 'array',
        'is_active' => 'boolean',
        'allow_anonymous' => 'boolean',
        'collect_company' => 'boolean',
        'collect_role' => 'boolean',
    ];

    protected $appends = [
        'public_url',
        'average_rating',
        'submissions_count_total',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($form) {
            if (empty($form->uuid)) {
                $form->uuid = (string) Str::uuid();
            }
            if (empty($form->slug)) {
                $form->slug = Str::slug($form->title) . '-' . Str::random(5);
            }
        });
    }

    public function submissions()
    {
        return $this->hasMany(FeedbackSubmission::class);
    }

    public function invitations()
    {
        return $this->hasMany(FeedbackInvitation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPublicUrlAttribute(): string
    {
        return route('feedback.show', ['identifier' => $this->slug]);
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->submissions()->avg('rating'), 1) ?: 0.0;
    }

    public function getSubmissionsCountTotalAttribute(): int
    {
        return $this->submissions()->count();
    }
}
