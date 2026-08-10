<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cultivos y fotos de cultivo.
     */
    public function up(): void
    {
        // ── Cultivos ───────────────────────────────────────────────────────────
        Schema::create('cultivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_lote');
            $table->unsignedBigInteger('id_variedad');
            $table->unsignedBigInteger('registrado_por');
            $table->string('codigo', 30)->unique();
            $table->string('estado', 20)->default('sembrado');
            // Valores: sembrado | creciendo | cosechado | perdido
            $table->date('fecha_siembra');
            $table->date('fecha_cosecha_estimada');
            $table->date('fecha_cosecha_real')->nullable();
            $table->decimal('cantidad_cosechada_kg', 10, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('fotografia', 500)->nullable();
            $table->unsignedBigInteger('activo_en_lote')->nullable()->unique();
            // activo_en_lote = id_lote cuando está activo, null cuando termina.
            // La unique constraint garantiza solo 1 cultivo activo por lote.
            $table->timestamps();

            $table->index('id_lote');
            $table->index('estado');
            $table->index('fecha_cosecha_estimada');

            $table->foreign('id_lote')->references('id')->on('lotes')->onDelete('restrict');
            $table->foreign('id_variedad')->references('id')->on('variedades')->onDelete('restrict');
            $table->foreign('registrado_por')->references('id')->on('usuarios')->onDelete('restrict');
        });

        // ── Fotos de Cultivo ───────────────────────────────────────────────────
        Schema::create('fotos_cultivo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cultivo');
            $table->unsignedBigInteger('id_usuario');
            $table->string('ruta', 500);
            $table->string('descripcion', 500)->nullable();
            $table->timestamp('fecha_captura')->useCurrent();

            $table->foreign('id_cultivo')->references('id')->on('cultivos')->onDelete('cascade');
            $table->foreign('id_usuario')->references('id')->on('usuarios')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fotos_cultivo');
        Schema::dropIfExists('cultivos');
    }
};
