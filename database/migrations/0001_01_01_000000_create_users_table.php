<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea las tablas base del sistema SIGVOS:
     * roles, permisos, roles_permisos, usuarios, tokens_recuperacion, sesiones.
     */
    public function up(): void
    {
        // ── Roles ──────────────────────────────────────────────────────────────
        Schema::create('roles', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('nombre', 30)->unique();
            $table->string('descripcion', 200)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
        });

        // ── Permisos ───────────────────────────────────────────────────────────
        Schema::create('permisos', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('codigo', 60)->unique();
            $table->string('descripcion', 200);
            $table->timestamp('creado_en')->useCurrent();
        });

        // ── Roles ↔ Permisos (pivot) ───────────────────────────────────────────
        Schema::create('roles_permisos', function (Blueprint $table) {
            $table->unsignedTinyInteger('id_rol');
            $table->unsignedSmallInteger('id_permiso');
            $table->primary(['id_rol', 'id_permiso']);
            $table->foreign('id_rol')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('id_permiso')->references('id')->on('permisos')->onDelete('cascade');
        });

        // ── Usuarios ───────────────────────────────────────────────────────────
        // Nota: los campos email, password, email_verified_at y remember_token
        // mantienen su nombre en inglés porque son usados internamente por
        // el sistema de autenticación de Laravel (Breeze).
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('id_rol')->default(2); // 2 = Trabajador
            $table->string('nombre', 100);
            $table->string('email', 150)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('telefono', 30)->nullable();
            $table->string('foto_perfil', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->tinyInteger('intentos_fallidos')->default(0);
            $table->dateTime('bloqueado_hasta')->nullable();
            $table->dateTime('ultimo_acceso')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['id_rol', 'activo']);
            $table->foreign('id_rol')->references('id')->on('roles')->onDelete('restrict');
        });

        // ── Tokens para recuperación de contraseña (Breeze) ───────────────────
        Schema::create('tokens_recuperacion', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('creado_en')->nullable();
        });

        // ── Sesiones (driver database de Laravel) ─────────────────────────────
        Schema::create('sesiones', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesiones');
        Schema::dropIfExists('tokens_recuperacion');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('roles_permisos');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('roles');
    }
};
