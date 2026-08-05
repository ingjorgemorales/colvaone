<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['data_policy_id', 'user_id', 'ip_address', 'user_agent', 'accepted_at'])]
class DataPolicyAcceptance extends BaseModel
{
    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }
}
