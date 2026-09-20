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
        'primary_domain',
        'username',
        'package_name',
        'status',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function server()
    {
        return $this->belongsTo(HostingServer::class, 'server_id');
    }
}
