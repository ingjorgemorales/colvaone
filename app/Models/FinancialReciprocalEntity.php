<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialReciprocalEntity extends Model
{
    use HasFactory;

    protected $table = 'financial_reciprocal_entities';

    protected $fillable = [
        'codigo_entidad_reciproca',
        'nombre_entidad_reciproca',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reciprocals(): HasMany
    {
        return $this->hasMany(FinancialReciprocal::class, 'codigo_entidad_reciproca', 'codigo_entidad_reciproca');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $clean = trim($term);
        return $query->where(function (Builder $q) use ($clean) {
            $q->where('codigo_entidad_reciproca', 'like', "%{$clean}%")
              ->orWhere('nombre_entidad_reciproca', 'like', "%{$clean}%");
        });
    }
}
