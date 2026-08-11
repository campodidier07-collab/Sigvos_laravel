<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Notificaciones del sistema para cada usuario.
     */
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_cultivo')->nullable();
            $table->unsignedBigInteger('id_actividad')->nullable();
            $table->string('tipo', 30);
            $table->string('prioridad', 10)->default('media');
            // Valores: baja | media | alta
            $table->string('titulo', 150);
            $table->string('mensaje', 500);
            $table->string('url', 255)->nullable();
            $table->boolean('leida')->default(false);
            $table->dateTime('leida_en')->nullable();
            $table->dateTime('programada_para')->nullable();
            $table->timestamp('creado_en')->useCurrent();

            $table->index(['id_usuario', 'leida', 'prioridad']);

            $table->foreign('id_usuario')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('id_cultivo')->references('id')->on('cultivos')->onDelete('set null');
            $table->foreign('id_actividad')->references('id')->on('actividades')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
