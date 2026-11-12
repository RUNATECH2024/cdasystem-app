<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('solicitudes_distribuidor')) {
            Schema::create('solicitudes_distribuidor', function (Blueprint $table) {
                $table->increments('id_solicitud');
                $table->string('ruc', 20);
                $table->string('representante_legal', 150);
                $table->boolean('actividad_tecnologia');
                $table->string('tiempo_negocio', 100);
                $table->string('provincia', 100);
                $table->string('canton', 100);
                $table->string('parroquia', 100);
                $table->string('calle_principal', 150);
                $table->string('numero_predio', 30);
                $table->string('calle_secundaria', 150)->nullable();
                $table->string('referencia_adicional', 255)->nullable();
                $table->string('telefono_local', 20);
                $table->string('celular', 20);
                $table->string('email_contacto', 150);
                $table->string('oficina', 100);
                $table->boolean('acepta_terminos')->default(false);
                $table->string('estado', 20)->default('Pendiente');
                $table->timestamp('fecha_solicitud')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        // No-op intencional: no borrar tablas con datos reales en un rollback.
    }
};
