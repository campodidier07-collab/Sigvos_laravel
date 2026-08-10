<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Variedad extends Model
{
    protected $table = 'variedades';
    public $timestamps = false;

    protected $fillable = [
        'id_tipo_cultivo',
        'nombre',
        'dias_cosecha_min',
        'dias_cosecha_max',
        'dias_cosecha_promedio',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /** Tipo de cultivo al que pertenece esta variedad. */
    public function tipoCultivo(): BelongsTo
    {
        return $this->belongsTo(TipoCultivo::class, 'id_tipo_cultivo');
    }

    /** Cultivos que usan esta variedad. */
    public function cultivos(): HasMany
    {
        return $this->hasMany(Cultivo::class, 'id_variedad');
    }
}
