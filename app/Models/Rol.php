<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'roles';
    public $timestamps = false;

    protected $fillable = ['nombre', 'descripcion', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /** Usuarios que tienen este rol. */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'id_rol');
    }

    /** Permisos asociados a este rol. */
    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(
            Permiso::class,
            'roles_permisos',
            'id_rol',
            'id_permiso'
        );
    }
}
