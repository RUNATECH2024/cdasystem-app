<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clientes')) {
            Schema::create('clientes', function (Blueprint $table) {
                $table->increments('id_cliente');
                $table->string('dni_ruc', 20)->nullable()->unique();
                $table->string('nombre', 100);
                $table->string('telefono', 20);
                $table->string('email', 100)->nullable();
                $table->string('direccion', 150)->nullable();
                $table->decimal('saldo_pendiente', 10, 2)->nullable()->default(0);
                $table->timestamp('fecha_registro')->nullable()->useCurrent();
                $table->boolean('activo')->nullable()->default(true);
                $table->string('password', 255)->nullable();
            });
        }

        if (! Schema::hasTable('usuarios')) {
            Schema::create('usuarios', function (Blueprint $table) {
                $table->increments('id_usuario');
                $table->string('nombre', 100);
                $table->string('usuario', 50)->unique();
                $table->string('password_hash', 255);
                $table->string('rol', 20)->nullable()->default('Vendedor');
                $table->boolean('activo')->nullable()->default(true);
                $table->timestamp('fecha_creacion')->nullable()->useCurrent();
            });
        }
    }

    public function down(): void
    {
        // No-op intencional: no borrar tablas con datos reales en un rollback.
    }
};
