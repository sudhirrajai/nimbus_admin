<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BugReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_key',
        'domain',
        'admin_name',
        'admin_email',
        'message',
        'screenshot_path',
        'images',
        'ip_address',
        'status',
    ];

    protected $casts = [
        'images' => 'array',
    ];
}
