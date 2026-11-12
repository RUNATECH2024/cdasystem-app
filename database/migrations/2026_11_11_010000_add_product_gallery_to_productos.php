<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('productos') && ! Schema::hasColumn('productos', 'imagenes_adicionales')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->json('imagenes_adicionales')->nullable();
            });
        }
    }

    public function down(): void
    {
        // No-op intencional: no eliminar columnas con datos reales en un rollback.
    }
};
