<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenServicio extends Model
{
    public $timestamps = false;

    protected $table = 'ordenes_servicio';

    protected $primaryKey = 'id_orden';

    protected $fillable = [
        'id_cliente', 'id_equipo', 'id_tecnico', 'fecha_ingreso', 'fecha_entrega',
        'falla_reportada', 'diagnostico', 'contador_ingreso', 'contador_salida',
        'estado', 'total_mano_obra', 'total_repuestos', 'total_general',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'datetime',
            'fecha_entrega' => 'datetime',
            'total_mano_obra' => 'decimal:2',
            'total_repuestos' => 'decimal:2',
            'total_general' => 'decimal:2',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function equipo()
    {
        return $this->belongsTo(EquipoCliente::class, 'id_equipo', 'id_equipo');
    }

    public function tecnico()
    {
        return $this->belongsTo(Usuario::class, 'id_tecnico', 'id_usuario');
    }

    public function repuestos()
    {
        return $this->hasMany(DetalleRepuestosOs::class, 'id_orden', 'id_orden');
    }
}
