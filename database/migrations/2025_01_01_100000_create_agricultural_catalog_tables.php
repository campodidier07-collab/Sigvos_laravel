<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo agrícola: tipos_cultivo, variedades, tipos_actividad.
     */
    public function up(): void
    {
        // ── Tipos de Cultivo ───────────────────────────────────────────────────
        Schema::create('tipos_cultivo', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('codigo', 15)->unique();
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 200)->nullable();
            $table->string('fotografia', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
        });

        // ── Variedades ─────────────────────────────────────────────────────────
        Schema::create('variedades', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('id_tipo_cultivo');
            $table->string('nombre', 100);
            $table->smallInteger('dias_cosecha_min');
            $table->smallInteger('dias_cosecha_max');
            $table->smallInteger('dias_cosecha_promedio');
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();

            $table->unique(['id_tipo_cultivo', 'nombre']);
            $table->foreign('id_tipo_cultivo')
                  ->references('id')->on('tipos_cultivo')
                  ->onDelete('restrict');
        });

        // ── Tipos de Actividad ─────────────────────────────────────────────────
        Schema::create('tipos_actividad', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('codigo', 25)->unique();
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 200)->nullable();
            $table->smallInteger('dias_recordatorio_previo')->default(1);
            $table->smallInteger('dias_frecuencia_sugerida')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_actividad');
        Schema::dropIfExists('variedades');
        Schema::dropIfExists('tipos_cultivo');
    }
};
