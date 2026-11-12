<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('productos')) {
            return;
        }

        if (! Schema::hasColumn('productos', 'condicion')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->string('condicion', 50)->nullable()->default('Nuevo');
            });
        }

        if (! Schema::hasColumn('productos', 'garantia_meses')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->integer('garantia_meses')->nullable()->default(0);
            });
        }
    }

    public function down(): void
    {
        // No-op intencional: no eliminar columnas con datos reales en un rollback.
    }
};
