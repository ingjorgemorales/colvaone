<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeReport extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'committee_id',
        'content',
        'registered_at',
        'created_by',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
    ];

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
