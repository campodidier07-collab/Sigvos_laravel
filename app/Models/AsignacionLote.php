<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionLote extends Model
{
    protected $table = 'asignaciones_lote';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_lote',
        'asignado_por',
        'activo',
        'clave_activa',
        'creado_en',
    ];

    protected function casts(): array
    {
        return [
            'activo'    => 'boolean',
            'creado_en' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'asignado_por');
    }
}
