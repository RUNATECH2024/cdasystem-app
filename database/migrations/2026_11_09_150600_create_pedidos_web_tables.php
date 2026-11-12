<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pedidos_web')) {
            Schema::create('pedidos_web', function (Blueprint $table) {
                $table->increments('id_pedido');
                $table->unsignedInteger('id_cliente')->nullable();
                $table->timestamp('fecha_pedido')->nullable()->useCurrent();
                $table->string('estado_pedido', 20)->nullable()->default('Pendiente');
                $table->decimal('subtotal', 10, 2);
                $table->decimal('costo_envio', 10, 2)->nullable()->default(0);
                $table->decimal('total', 10, 2);
                $table->string('metodo_pago', 50)->nullable();
                $table->string('direccion_envio', 255)->nullable();
                $table->string('telefono_contacto', 20)->nullable();

                $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
            });
        }

        if (! Schema::hasTable('detalle_pedidos_web')) {
            Schema::create('detalle_pedidos_web', function (Blueprint $table) {
                $table->increments('id_detalle');
                $table->unsignedInteger('id_pedido');
                $table->unsignedInteger('id_producto');
                $table->integer('cantidad');
                $table->decimal('precio_unitario', 10, 2);

                $table->foreign('id_pedido')->references('id_pedido')->on('pedidos_web')->cascadeOnDelete();
                $table->foreign('id_producto')->references('id_producto')->on('productos');
            });
        }

        if (! Schema::hasTable('garantias')) {
            Schema::create('garantias', function (Blueprint $table) {
                $table->increments('id_garantia');
                $table->unsignedInteger('id_detalle_venta')->nullable();
                $table->unsignedInteger('id_detalle_pedido')->nullable();
                $table->string('numero_serie_equipo', 50)->nullable();
                $table->date('fecha_inicio');
                $table->date('fecha_fin');
                $table->string('estado', 20)->nullable()->default('Activa');
                $table->text('observaciones')->nullable();
            });
        }
    }

    public function down(): void
    {
        // No-op intencional: no borrar tablas con datos reales en un rollback.
    }
};
