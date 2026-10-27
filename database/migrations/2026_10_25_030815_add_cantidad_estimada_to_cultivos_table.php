<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cultivos', function (Blueprint $table) {
            $table->decimal('cantidad_estimada_kg', 10, 2)->nullable()->after('fecha_cosecha_estimada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cultivos', function (Blueprint $table) {
            $table->dropColumn('cantidad_estimada_kg');
        });
    }
};
