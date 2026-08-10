<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permiso extends Model
{
    protected $table = 'permisos';
    public $timestamps = false;

    protected $fillable = ['codigo', 'descripcion'];

    /** Roles que tienen este permiso. */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Rol::class,
            'roles_permisos',
            'id_permiso',
            'id_rol'
        );
    }
}
