<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('compras')) {
            Schema::create('compras', function (Blueprint $table) {
                $table->increments('id_compra');
                $table->unsignedInteger('id_proveedor');
                $table->unsignedInteger('id_usuario')->nullable();
                $table->string('numero_factura', 50)->nullable();
                $table->timestamp('fecha_compra')->nullable()->useCurrent();
                $table->date('fecha_vencimiento')->nullable();
                $table->decimal('subtotal', 10, 2);
                $table->decimal('impuesto', 10, 2)->nullable()->default(0);
                $table->decimal('total', 10, 2);
                $table->string('estado', 20)->nullable()->default('Pagada');

                $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedores');
                $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
            });
        }

        if (! Schema::hasTable('detalle_compras')) {
            Schema::create('detalle_compras', function (Blueprint $table) {
                $table->increments('id_detalle');
                $table->unsignedInteger('id_compra');
                $table->unsignedInteger('id_producto');
                $table->integer('cantidad');
                $table->decimal('precio_costo_unitario', 10, 2);

                $table->foreign('id_compra')->references('id_compra')->on('compras')->cascadeOnDelete();
                $table->foreign('id_producto')->references('id_producto')->on('productos');
            });
        }

        if (! Schema::hasTable('abonos')) {
            Schema::create('abonos', function (Blueprint $table) {
                $table->increments('id_abono');
                $table->unsignedInteger('id_venta')->nullable();
                $table->unsignedInteger('id_compra')->nullable();
                $table->decimal('monto', 10, 2);
                $table->timestamp('fecha_abono')->nullable()->useCurrent();
                $table->string('metodo_pago', 50)->nullable();
                $table->unsignedInteger('id_usuario')->nullable();

                $table->foreign('id_venta')->references('id_venta')->on('ventas');
                $table->foreign('id_compra')->references('id_compra')->on('compras');
                $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
            });
        }
    }

    public function down(): void
    {
        // No-op intencional: no borrar tablas con datos reales en un rollback.
    }
};
