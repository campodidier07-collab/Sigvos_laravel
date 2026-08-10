<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lotes y asignaciones de lotes a usuarios.
     */
    public function up(): void
    {
        // ── Lotes ──────────────────────────────────────────────────────────────
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->char('identificador', 1)->unique();       // Ej: A, B, C
            $table->string('nombre', 100);
            $table->string('ubicacion', 200);
            $table->decimal('area_ha', 6, 2);
            $table->unsignedTinyInteger('id_tipo_preferido')->nullable();
            $table->string('fotografia', 255)->nullable();
            $table->boolean('es_alternativo')->default(false);
            $table->string('estado', 20)->default('disponible');
            // Valores: disponible | ocupado | mantenimiento
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('estado');
            $table->foreign('id_tipo_preferido')
                  ->references('id')->on('tipos_cultivo')
                  ->onDelete('set null');
        });

        // ── Asignaciones Lote → Usuario ────────────────────────────────────────
        Schema::create('asignaciones_lote', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_lote');
            $table->unsignedBigInteger('asignado_por');
            $table->boolean('activo')->default(true);
            $table->string('clave_activa', 40)->nullable()->unique();
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('id_usuario')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('id_lote')->references('id')->on('lotes')->onDelete('cascade');
            $table->foreign('asignado_por')->references('id')->on('usuarios')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_lote');
        Schema::dropIfExists('lotes');
    }
};
