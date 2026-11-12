<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoWeb extends Model
{
    public $timestamps = false;

    protected $table = 'pedidos_web';

    protected $primaryKey = 'id_pedido';

    protected $fillable = [
        'id_cliente', 'fecha_pedido', 'estado_pedido', 'subtotal',
        'costo_envio', 'total', 'metodo_pago', 'direccion_envio', 'telefono_contacto',
        'nombre_contacto', 'dni_ruc', 'empresa', 'email_contacto', 'provincia',
        'ciudad', 'referencia_envio', 'informacion_adicional', 'metodo_entrega',
    ];

    protected function casts(): array
    {
        return [
            'fecha_pedido' => 'datetime',
            'subtotal' => 'decimal:2',
            'costo_envio' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedidoWeb::class, 'id_pedido', 'id_pedido');
    }
}
