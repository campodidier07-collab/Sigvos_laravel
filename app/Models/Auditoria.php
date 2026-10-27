<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    protected $table = 'auditoria';
    public $timestamps = false;

    protected $fillable = [
        'tabla',
        'id_registro',
        'accion',
        'realizado_por',
        'datos_antes',
        'datos_despues',
        'ip_origen',
        'ocurrido_en',
    ];

    protected function casts(): array
    {
        return [
            'datos_antes'   => 'array',
            'datos_despues' => 'array',
            'ocurrido_en'   => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'realizado_por');
    }
}
