<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id_audit');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->string('actor_usuario', 50)->nullable();
            $table->string('actor_rol', 20)->nullable();
            $table->string('accion', 100);
            $table->string('metodo_http', 10);
            $table->string('ruta', 150)->nullable();
            $table->string('objetivo', 150)->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedSmallInteger('codigo_respuesta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['id_usuario', 'created_at']);
            $table->index(['accion', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
    }
};
