<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudDistribuidor extends Model
{
    public $timestamps = false;

    protected $table = 'solicitudes_distribuidor';

    protected $primaryKey = 'id_solicitud';

    protected $fillable = [
        'ruc', 'representante_legal', 'actividad_tecnologia', 'tiempo_negocio',
        'provincia', 'canton', 'parroquia', 'calle_principal', 'numero_predio',
        'calle_secundaria', 'referencia_adicional', 'telefono_local', 'celular',
        'email_contacto', 'oficina', 'acepta_terminos', 'estado', 'fecha_solicitud',
    ];

    protected function casts(): array
    {
        return [
            'actividad_tecnologia' => 'boolean',
            'acepta_terminos' => 'boolean',
            'fecha_solicitud' => 'datetime',
        ];
    }
}
