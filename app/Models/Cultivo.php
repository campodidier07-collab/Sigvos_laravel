<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cultivo extends Model
{
    protected $table = 'cultivos';

    protected $fillable = [
        'id_lote',
        'id_variedad',
        'registrado_por',
        'codigo',
        'estado',
        'fecha_siembra',
        'fecha_cosecha_estimada',
        'fecha_cosecha_real',
        'cantidad_cosechada_kg',
        'observaciones',
        'fotografia',
        'activo_en_lote',
    ];

    protected function casts(): array
    {
        return [
            'fecha_siembra'            => 'date',
            'fecha_cosecha_estimada'   => 'date',
            'fecha_cosecha_real'       => 'date',
            'cantidad_cosechada_kg'    => 'decimal:2',
        ];
    }

    /** Lote donde está sembrado el cultivo. */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    /** Variedad sembrada. */
    public function variedad(): BelongsTo
    {
        return $this->belongsTo(Variedad::class, 'id_variedad');
    }

    /** Usuario que registró el cultivo. */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'registrado_por');
    }

    /** Actividades asociadas a este cultivo. */
    public function actividades(): HasMany
    {
        return $this->hasMany(Actividad::class, 'id_cultivo');
    }

    /** Galería de fotos del cultivo. */
    public function fotos(): HasMany
    {
        return $this->hasMany(FotoCultivo::class, 'id_cultivo');
    }

    /** Notificaciones relacionadas con este cultivo. */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'id_cultivo');
    }

    /** ¿El cultivo está activo (sembrado/creciendo)? */
    public function estaActivo(): bool
    {
        return ! is_null($this->activo_en_lote);
    }
}
