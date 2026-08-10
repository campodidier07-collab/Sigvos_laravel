<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoActividad extends Model
{
    protected $table = 'tipos_actividad';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'dias_recordatorio_previo',
        'dias_frecuencia_sugerida',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /** Actividades de este tipo. */
    public function actividades(): HasMany
    {
        return $this->hasMany(Actividad::class, 'id_tipo_actividad');
    }
}
