<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('almacenes')) {
            Schema::create('almacenes', function (Blueprint $table) {
                $table->increments('id_almacen');
                $table->string('nombre', 50);
                $table->string('ubicacion', 100)->nullable();
                $table->boolean('activo')->nullable()->default(true);
            });
        }

        if (! Schema::hasTable('proveedores')) {
            Schema::create('proveedores', function (Blueprint $table) {
                $table->increments('id_proveedor');
                $table->string('ruc', 20)->nullable();
                $table->string('nombre', 100);
                $table->string('telefono', 20)->nullable();
                $table->string('email', 100)->nullable();
                $table->string('direccion', 150)->nullable();
                $table->decimal('saldo_pendiente', 10, 2)->nullable()->default(0);
                $table->boolean('activo')->nullable()->default(true);
            });
        }

        if (! Schema::hasTable('inventario_almacen')) {
            Schema::create('inventario_almacen', function (Blueprint $table) {
                $table->increments('id_inventario');
                $table->unsignedInteger('id_producto');
                $table->unsignedInteger('id_almacen');
                $table->integer('stock')->default(0);

                $table->unique(['id_producto', 'id_almacen']);
                $table->foreign('id_producto')->references('id_producto')->on('productos');
                $table->foreign('id_almacen')->references('id_almacen')->on('almacenes');
            });
        }

        if (! Schema::hasTable('movimientos_inventario')) {
            Schema::create('movimientos_inventario', function (Blueprint $table) {
                $table->increments('id_movimiento');
                $table->unsignedInteger('id_producto');
                $table->unsignedInteger('id_almacen')->nullable();
                $table->string('tipo_movimiento', 20);
                $table->string('origen', 30);
                $table->integer('cantidad');
                $table->integer('stock_anterior');
                $table->integer('stock_nuevo');
                $table->text('motivo')->nullable();
                $table->unsignedInteger('id_referencia')->nullable();
                $table->unsignedInteger('id_usuario')->nullable();
                $table->timestamp('fecha_movimiento')->nullable()->useCurrent();
            });
        }
    }

    public function down(): void
    {
        // No-op intencional: no borrar tablas con datos reales en un rollback.
    }
};
