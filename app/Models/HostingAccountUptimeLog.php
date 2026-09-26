<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostingAccountUptimeLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'hosting_account_id',
        'status',
        'status_code',
        'response_time_ms',
        'error_message',
        'created_at',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'response_time_ms' => 'integer',
        'created_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(HostingAccount::class, 'hosting_account_id');
    }
}
