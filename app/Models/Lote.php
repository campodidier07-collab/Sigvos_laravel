<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lote extends Model
{
    protected $table = 'lotes';

    protected $fillable = [
        'identificador',
        'nombre',
        'ubicacion',
        'area_ha',
        'id_tipo_preferido',
        'fotografia',
        'es_alternativo',
        'estado',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'area_ha'        => 'decimal:2',
            'es_alternativo' => 'boolean',
            'activo'         => 'boolean',
        ];
    }

    /** Tipo de cultivo preferido para este lote. */
    public function tipoPreferido(): BelongsTo
    {
        return $this->belongsTo(TipoCultivo::class, 'id_tipo_preferido');
    }

    /** Trabajadores asignados a este lote. */
    public function trabajadores(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'asignaciones_lote',
            'id_lote',
            'id_usuario'
        )->withPivot(['activo', 'clave_activa', 'asignado_por', 'creado_en'])
         ->wherePivot('activo', true);
    }

    /** Historial de cultivos de este lote. */
    public function cultivos(): HasMany
    {
        return $this->hasMany(Cultivo::class, 'id_lote');
    }

    /** Cultivo activo actualmente en el lote. */
    public function cultivoActivo()
    {
        return $this->hasOne(Cultivo::class, 'id_lote')
                    ->whereNotNull('activo_en_lote');
    }
}
