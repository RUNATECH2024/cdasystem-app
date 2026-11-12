<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categorias')) {
            Schema::create('categorias', function (Blueprint $table) {
                $table->increments('id_categoria');
                $table->string('nombre', 50);
                $table->string('icono', 50)->nullable();
                $table->integer('orden_visual')->nullable()->default(0);
                $table->boolean('activo')->nullable()->default(true);
            });
        }

        if (! Schema::hasTable('subcategorias')) {
            Schema::create('subcategorias', function (Blueprint $table) {
                $table->increments('id_subcategoria');
                $table->unsignedInteger('id_categoria');
                $table->string('nombre', 50);
                $table->string('imagen_portada', 255)->nullable();
                $table->integer('orden_visual')->nullable()->default(0);
                $table->boolean('activo')->nullable()->default(true);

                $table->foreign('id_categoria')->references('id_categoria')->on('categorias');
            });
        }

        if (! Schema::hasTable('productos')) {
            Schema::create('productos', function (Blueprint $table) {
                $table->increments('id_producto');
                $table->unsignedInteger('id_subcategoria')->nullable();
                $table->string('codigo_barras', 50)->nullable()->unique();
                $table->string('sku', 50)->nullable()->unique();
                $table->string('nombre', 150);
                $table->string('descripcion_corta', 255)->nullable();
                $table->text('descripcion_larga')->nullable();
                $table->decimal('precio_compra', 10, 2)->default(0);
                $table->decimal('precio_venta', 10, 2);
                $table->integer('stock_actual')->default(0);
                $table->integer('stock_minimo')->nullable()->default(5);
                $table->date('fecha_vencimiento')->nullable();
                $table->string('imagen_principal', 255)->nullable();
                $table->boolean('destacado')->nullable()->default(false);
                $table->boolean('es_repuesto')->nullable()->default(false);
                $table->boolean('activo')->nullable()->default(true);
                $table->timestamp('fecha_creacion')->nullable()->useCurrent();

                $table->foreign('id_subcategoria')->references('id_subcategoria')->on('subcategorias')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('atributos_producto')) {
            Schema::create('atributos_producto', function (Blueprint $table) {
                $table->increments('id_atributo');
                $table->unsignedInteger('id_producto');
                $table->string('nombre_atributo', 50);
                $table->string('valor_atributo', 100);
            });
        }
    }

    public function down(): void
    {
        // No-op intencional: el esquema vive sobre una base externa con datos reales.
        // Para reconstruir usa `migrate:fresh`, nunca `migrate:rollback` en producción.
    }
};
