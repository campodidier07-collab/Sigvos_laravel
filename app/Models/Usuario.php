<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /** Atributos computados que se incluyen en la serialización JSON */
    protected $appends = ['name', 'role', 'foto_perfil_url'];

    /** Nombre de la tabla en la base de datos. */
    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_rol',
        'nombre',
        'email',
        'password',
        'telefono',
        'foto_perfil',
        'activo',
        'intentos_fallidos',
        'bloqueado_hasta',
        'ultimo_acceso',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'bloqueado_hasta'   => 'datetime',
            'ultimo_acceso'     => 'datetime',
            'password'          => 'hashed',
            'activo'            => 'boolean',
            'intentos_fallidos' => 'integer',
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Relaciones
    // ──────────────────────────────────────────────────────────────────────────

    /** El rol asignado al usuario (Administrador / Trabajador). */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    /** Lotes asignados activamente a este usuario (como trabajador). */
    public function lotes(): BelongsToMany
    {
        return $this->belongsToMany(Lote::class, 'asignaciones_lote', 'id_usuario', 'id_lote')
                    ->withPivot(['id', 'activo', 'clave_activa', 'asignado_por', 'creado_en'])
                    ->wherePivot('activo', true);
    }

    /** Historial de asignaciones de lotes para este usuario. */
    public function asignacionesLote(): HasMany
    {
        return $this->hasMany(AsignacionLote::class, 'id_usuario')->orderByDesc('creado_en');
    }

    /** Cultivos registrados por este usuario. */
    public function cultivosRegistrados(): HasMany
    {
        return $this->hasMany(Cultivo::class, 'registrado_por');
    }

    /** Actividades creadas por este usuario. */
    public function actividadesCreadas(): HasMany
    {
        return $this->hasMany(Actividad::class, 'creado_por');
    }

    /** Actividades asignadas a este usuario. */
    public function actividadesAsignadas(): HasMany
    {
        return $this->hasMany(Actividad::class, 'asignado_a');
    }

    /** Historial de actividades completadas por este usuario. */
    public function actividadesCompletadas(): HasMany
    {
        return $this->hasMany(Actividad::class, 'asignado_a')->where('estado', 'completada')->orderBy('fecha_programada', 'desc');
    }

    /** Notificaciones del usuario. */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'id_usuario');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers de Rol y Permisos
    // ──────────────────────────────────────────────────────────────────────────

    /** ¿Es administrador? */
    public function esAdministrador(): bool
    {
        return $this->id_rol === 1;
    }

    /** ¿Es trabajador? */
    public function esTrabajador(): bool
    {
        return $this->id_rol === 2;
    }

    /**
     * Comprueba si el usuario tiene un permiso determinado (por código).
     * Alias compatible con el layout.
     */
    public function isAdmin(): bool
    {
        return $this->esAdministrador();
    }

    /**
     * Verifica si el usuario tiene un permiso específico (por código).
     */
    public function tienePermiso(string $codigoPermiso): bool
    {
        static $cache = [];
        $key = $this->id . '_' . $codigoPermiso;

        if (! isset($cache[$key])) {
            $cache[$key] = $this->rol
                ?->permisos()
                ->where('codigo', $codigoPermiso)
                ->exists() ?? false;
        }

        return $cache[$key];
    }

    /** Accessor para compatibilidad con API / Flutter: name */
    public function getNameAttribute(): string
    {
        return $this->nombre ?? '';
    }

    /** Accessor para compatibilidad con API / Flutter: role */
    public function getRoleAttribute(): string
    {
        return strtolower($this->rol?->nombre ?? ($this->id_rol === 1 ? 'administrador' : 'trabajador'));
    }

    /** Accessor URL de foto de perfil */
    public function getFotoPerfilUrlAttribute(): ?string
    {
        return $this->foto_perfil ? asset('storage/' . $this->foto_perfil) : null;
    }
}
