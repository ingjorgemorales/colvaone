<?php

namespace App\Models;

class IncidentOption extends BaseModel
{
    public const TYPES = [
        'area' => 'Áreas / dependencias',
        'zone' => 'Pisos o zonas',
        'failure' => 'Tipos de falla',
        'impact' => 'Afectación a la operación',
        'priority' => 'Prioridades',
    ];

    protected $fillable = ['type', 'name', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }
}
