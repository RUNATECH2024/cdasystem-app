<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('servicios')) {
            Schema::create('servicios', function (Blueprint $table) {
                $table->increments('id_servicio');
                $table->string('descripcion', 150);
                $table->decimal('precio', 10, 2);
                $table->string('duracion_estimada', 50)->nullable();
            });
        }

        if (! Schema::hasTable('equipos_cliente')) {
            Schema::create('equipos_cliente', function (Blueprint $table) {
                $table->increments('id_equipo');
                $table->unsignedInteger('id_cliente');
                $table->string('tipo_equipo', 30);
                $table->string('marca', 50)->nullable();
                $table->string('modelo', 50)->nullable();
                $table->string('numero_serie', 50)->nullable()->unique();
                $table->integer('contador_paginas')->nullable()->default(0);
                $table->text('observaciones')->nullable();

                $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('ordenes_servicio')) {
            Schema::create('ordenes_servicio', function (Blueprint $table) {
                $table->increments('id_orden');
                $table->unsignedInteger('id_cliente');
                $table->unsignedInteger('id_equipo');
                $table->unsignedInteger('id_tecnico')->nullable();
                $table->timestamp('fecha_ingreso')->nullable()->useCurrent();
                $table->timestamp('fecha_entrega')->nullable();
                $table->text('falla_reportada');
                $table->text('diagnostico')->nullable();
                $table->integer('contador_ingreso')->nullable();
                $table->integer('contador_salida')->nullable();
                $table->string('estado', 30)->nullable()->default('Pendiente');
                $table->decimal('total_mano_obra', 10, 2)->nullable()->default(0);
                $table->decimal('total_repuestos', 10, 2)->nullable()->default(0);
                $table->decimal('total_general', 10, 2)->nullable()->default(0);

                $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
                $table->foreign('id_equipo')->references('id_equipo')->on('equipos_cliente');
                $table->foreign('id_tecnico')->references('id_usuario')->on('usuarios');
            });
        }

        if (! Schema::hasTable('detalle_repuestos_os')) {
            Schema::create('detalle_repuestos_os', function (Blueprint $table) {
                $table->increments('id_detalle');
                $table->unsignedInteger('id_orden');
                $table->unsignedInteger('id_producto');
                $table->integer('cantidad');
                $table->decimal('precio_unitario', 10, 2);

                $table->foreign('id_orden')->references('id_orden')->on('ordenes_servicio')->cascadeOnDelete();
                $table->foreign('id_producto')->references('id_producto')->on('productos');
            });
        }
    }

    public function down(): void
    {
        // No-op intencional: no borrar tablas con datos reales en un rollback.
    }
};
