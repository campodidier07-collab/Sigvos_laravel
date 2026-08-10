<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Actividad extends Model
{
    protected $table = 'actividades';

    protected $fillable = [
        'id_cultivo',
        'id_tipo_actividad',
        'creado_por',
        'asignado_a',
        'ejecutado_por',
        'estado',
        'fecha_programada',
        'fecha_ejecucion',
        'descripcion',
        'observaciones',
        'fotografia',
    ];

    protected function casts(): array
    {
        return [
            'fecha_programada' => 'date',
            'fecha_ejecucion'  => 'date',
        ];
    }

    /** Cultivo al que pertenece esta actividad. */
    public function cultivo(): BelongsTo
    {
        return $this->belongsTo(Cultivo::class, 'id_cultivo');
    }

    /** Tipo de actividad (Riego, Poda, Cosecha…). */
    public function tipoActividad(): BelongsTo
    {
        return $this->belongsTo(TipoActividad::class, 'id_tipo_actividad');
    }

    /** Usuario que creó la actividad. */
    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    /** Usuario al que está asignada la actividad. */
    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'asignado_a');
    }

    /** Usuario que ejecutó la actividad. */
    public function ejecutadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'ejecutado_por');
    }

    /** Notificaciones vinculadas a esta actividad. */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'id_actividad');
    }
}
