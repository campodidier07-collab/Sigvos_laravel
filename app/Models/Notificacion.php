<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    /** La tabla usa 'creado_en' en vez de 'created_at'. */
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_usuario',
        'id_cultivo',
        'id_actividad',
        'tipo',
        'prioridad',
        'titulo',
        'mensaje',
        'url',
        'leida',
        'leida_en',
        'programada_para',
    ];

    protected function casts(): array
    {
        return [
            'leida'           => 'boolean',
            'leida_en'        => 'datetime',
            'programada_para' => 'datetime',
            'creado_en'       => 'datetime',
        ];
    }

    /** Usuario destinatario de la notificación. */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    /** Cultivo relacionado (opcional). */
    public function cultivo(): BelongsTo
    {
        return $this->belongsTo(Cultivo::class, 'id_cultivo');
    }

    /** Actividad relacionada (opcional). */
    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'id_actividad');
    }
}
