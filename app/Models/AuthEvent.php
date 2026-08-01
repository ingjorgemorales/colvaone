<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'email',
    'event',
    'successful',
    'ip_address',
    'user_agent',
    'route',
    'request_id',
    'reason',
    'occurred_at',
])]
class AuthEvent extends Model
{
    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
            'occurred_at' => 'datetime',
        ];
    }
}
