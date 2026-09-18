<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class BscPerspective extends BaseModel
{
    protected $fillable = ['name', 'position', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function indicators(): HasMany
    {
        return $this->hasMany(Indicator::class);
    }

    /** Las perspectivas activas que se ofrecen en la ficha tecnica. */
    public function scopeSelectable($query)
    {
        return $query->where('is_active', true)->orderBy('position')->orderBy('name');
    }
}
