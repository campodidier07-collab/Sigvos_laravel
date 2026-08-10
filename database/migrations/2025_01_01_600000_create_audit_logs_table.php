<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Auditoría de cambios: registra todas las operaciones en la BD.
     */
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->string('tabla', 64);
            $table->string('id_registro', 64);
            $table->string('accion', 10);
            // Valores: INSERT | UPDATE | DELETE
            $table->unsignedBigInteger('realizado_por')->nullable();
            $table->json('datos_antes')->nullable();
            $table->json('datos_despues')->nullable();
            $table->string('ip_origen', 45)->nullable();
            $table->timestamp('ocurrido_en')->useCurrent();

            $table->foreign('realizado_por')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
