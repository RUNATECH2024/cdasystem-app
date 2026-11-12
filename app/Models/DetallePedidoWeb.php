<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePedidoWeb extends Model
{
    public $timestamps = false;

    protected $table = 'detalle_pedidos_web';

    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'id_pedido', 'id_producto', 'cantidad', 'precio_unitario',
    ];

    protected function casts(): array
    {
        return [
            'precio_unitario' => 'decimal:2',
        ];
    }

    public function pedido()
    {
        return $this->belongsTo(PedidoWeb::class, 'id_pedido', 'id_pedido');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function garantias()
    {
        return $this->hasMany(Garantia::class, 'id_detalle_pedido', 'id_detalle');
    }
}
