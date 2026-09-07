<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Subprocess extends BaseModel
{
    protected $fillable = ['code', 'name', 'position', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function indicators(): HasMany
    {
        return $this->hasMany(Indicator::class);
    }

    /** "A07. GESTION DE COMUNICACIONES" cuando hay codigo; si no, solo el nombre. */
    public function getFullNameAttribute(): string
    {
        return $this->code ? "{$this->code}. {$this->name}" : $this->name;
    }

    public function scopeSelectable($query)
    {
        return $query->where('is_active', true)->orderBy('position')->orderBy('name');
    }
}
