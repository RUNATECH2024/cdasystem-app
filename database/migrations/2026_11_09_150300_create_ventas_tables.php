<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ventas')) {
            Schema::create('ventas', function (Blueprint $table) {
                $table->increments('id_venta');
                $table->unsignedInteger('id_cliente')->nullable();
                $table->unsignedInteger('id_vendedor')->nullable();
                $table->timestamp('fecha')->nullable()->useCurrent();
                $table->date('fecha_vencimiento')->nullable();
                $table->decimal('subtotal', 10, 2);
                $table->decimal('impuesto', 10, 2);
                $table->decimal('total', 10, 2);
                $table->string('metodo_pago', 20)->nullable()->default('Efectivo');
                $table->string('estado', 20)->nullable()->default('Pagada');

                $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
                $table->foreign('id_vendedor')->references('id_usuario')->on('usuarios');
            });
        }

        if (! Schema::hasTable('detalle_ventas')) {
            Schema::create('detalle_ventas', function (Blueprint $table) {
                $table->increments('id_detalle');
                $table->unsignedInteger('id_venta');
                $table->unsignedInteger('id_producto');
                $table->integer('cantidad');
                $table->decimal('precio_unitario', 10, 2);

                $table->foreign('id_venta')->references('id_venta')->on('ventas')->cascadeOnDelete();
                $table->foreign('id_producto')->references('id_producto')->on('productos');
            });
        }
    }

    public function down(): void
    {
        // No-op intencional: no borrar tablas con datos reales en un rollback.
    }
};
