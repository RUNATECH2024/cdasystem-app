<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'admin_audit_logs';

    protected $primaryKey = 'id_audit';

    protected $fillable = [
        'id_usuario', 'actor_usuario', 'actor_rol', 'accion', 'metodo_http',
        'ruta', 'objetivo', 'metadata', 'codigo_respuesta', 'ip', 'user_agent', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
