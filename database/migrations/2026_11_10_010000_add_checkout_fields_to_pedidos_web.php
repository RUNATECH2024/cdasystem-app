<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columnas = [
            'nombre_contacto' => fn (Blueprint $table) => $table->string('nombre_contacto', 150)->nullable(),
            'dni_ruc' => fn (Blueprint $table) => $table->string('dni_ruc', 20)->nullable(),
            'empresa' => fn (Blueprint $table) => $table->string('empresa', 150)->nullable(),
            'email_contacto' => fn (Blueprint $table) => $table->string('email_contacto', 150)->nullable(),
            'provincia' => fn (Blueprint $table) => $table->string('provincia', 100)->nullable(),
            'ciudad' => fn (Blueprint $table) => $table->string('ciudad', 100)->nullable(),
            'referencia_envio' => fn (Blueprint $table) => $table->string('referencia_envio', 255)->nullable(),
            'informacion_adicional' => fn (Blueprint $table) => $table->string('informacion_adicional', 255)->nullable(),
            'metodo_entrega' => fn (Blueprint $table) => $table->string('metodo_entrega', 60)->nullable(),
        ];

        foreach ($columnas as $nombre => $definicion) {
            if (! Schema::hasColumn('pedidos_web', $nombre)) {
                Schema::table('pedidos_web', $definicion);
            }
        }
    }

    public function down(): void
    {
        // No-op intencional: no borrar columnas con datos reales en un rollback.
    }
};
