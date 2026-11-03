<?php

namespace App\Models;

class IncidentOption extends BaseModel
{
    public const TYPES = [
        'area' => 'Áreas / dependencias',
        'zone' => 'Sedes',
        'failure' => 'Tipos de falla',
        'prioritized_risk' => 'Riesgos priorizados',
        'impact' => 'Afectación a la operación',
        'priority' => 'Prioridades',
    ];

    protected $fillable = ['type', 'name', 'sort_order', 'is_active', 'responsible_position'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }
}
