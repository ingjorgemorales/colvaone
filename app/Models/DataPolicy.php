<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['version', 'published_at', 'is_active', 'content'])]
class DataPolicy extends BaseModel
{
    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'is_active' => 'boolean',
        ];
    }
}
