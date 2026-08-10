<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoCultivo extends Model
{
    protected $table = 'tipos_cultivo';
    public $timestamps = false;

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'fotografia', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /** Variedades disponibles para este tipo de cultivo. */
    public function variedades(): HasMany
    {
        return $this->hasMany(Variedad::class, 'id_tipo_cultivo');
    }

    /** Lotes que prefieren este tipo de cultivo. */
    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'id_tipo_preferido');
    }
}
