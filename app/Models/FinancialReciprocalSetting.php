<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialReciprocalSetting extends BaseModel
{
    protected $table = 'financial_reciprocal_settings';

    protected $fillable = [
        'departamento',
        'municipio',
        'entidad',
        'codigo',
        'fecha_corte',
        'formato',
        'cifras',
        'updated_by',
    ];

    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'departamento' => 'CUNDINAMARCA',
            'municipio' => 'BOGOTA DISTRITO CAPITAL',
            'entidad' => 'COMPAÑÍA COLOMBIANA DE SERVICIOS DE VALOR AGREGADO Y TELEMÁTICOS COLVATEL S.A. ESP',
            'codigo' => '269411001',
            'fecha_corte' => '1 Abril a 30 de Junio 2026',
            'formato' => 'CGN2005_002_SALDO_DE_OPERACIONES RECIPROCAS',
            'cifras' => 'Cifras en Miles de Pesos',
        ]);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

