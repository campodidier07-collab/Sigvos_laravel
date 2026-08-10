<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoCultivo extends Model
{
    protected $table = 'fotos_cultivo';
    public $timestamps = false;

    protected $fillable = [
        'id_cultivo',
        'id_usuario',
        'ruta',
        'descripcion',
        'fecha_captura',
    ];

    protected function casts(): array
    {
        return ['fecha_captura' => 'datetime'];
    }

    /** Cultivo al que pertenece esta foto. */
    public function cultivo(): BelongsTo
    {
        return $this->belongsTo(Cultivo::class, 'id_cultivo');
    }

    /** Usuario que subió la foto. */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
