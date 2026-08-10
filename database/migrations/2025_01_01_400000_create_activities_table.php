<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Actividades agrícolas programadas y ejecutadas sobre cultivos.
     */
    public function up(): void
    {
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cultivo');
            $table->unsignedTinyInteger('id_tipo_actividad');
            $table->unsignedBigInteger('creado_por');
            $table->unsignedBigInteger('asignado_a')->nullable();
            $table->unsignedBigInteger('ejecutado_por')->nullable();
            $table->string('estado', 20)->default('pendiente');
            // Valores: pendiente | en_progreso | completada | cancelada
            $table->date('fecha_programada')->nullable();
            $table->date('fecha_ejecucion')->nullable();
            $table->string('descripcion', 500);
            $table->text('observaciones')->nullable();
            $table->string('fotografia', 500)->nullable();
            $table->timestamps();

            $table->index('id_cultivo');
            $table->index(['asignado_a', 'estado']);
            $table->index(['fecha_programada', 'estado']);

            $table->foreign('id_cultivo')->references('id')->on('cultivos')->onDelete('cascade');
            $table->foreign('id_tipo_actividad')->references('id')->on('tipos_actividad')->onDelete('restrict');
            $table->foreign('creado_por')->references('id')->on('usuarios')->onDelete('restrict');
            $table->foreign('asignado_a')->references('id')->on('usuarios')->onDelete('set null');
            $table->foreign('ejecutado_por')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
