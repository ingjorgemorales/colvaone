<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['data_policy_id', 'user_id', 'ip_address', 'user_agent', 'accepted_at'])]
class DataPolicyAcceptance extends Model
{
    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }
}
