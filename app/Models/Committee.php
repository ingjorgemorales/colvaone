<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Committee extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'committee_date',
        'summary',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'committee_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'committee_user')
            ->withTimestamps();
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'active' ? 'Activo' : 'Inactivo';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status === 'active' ? '#059669' : '#94a3b8';
    }
}
