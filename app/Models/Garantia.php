<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Garantia extends Model
{
    public $timestamps = false;

    protected $table = 'garantias';

    protected $primaryKey = 'id_garantia';

    protected $fillable = [
        'id_detalle_venta', 'id_detalle_pedido', 'numero_serie_equipo',
        'fecha_inicio', 'fecha_fin', 'estado', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function detalleVenta()
    {
        return $this->belongsTo(DetalleVenta::class, 'id_detalle_venta', 'id_detalle');
    }

    public function detallePedido()
    {
        return $this->belongsTo(DetallePedidoWeb::class, 'id_detalle_pedido', 'id_detalle');
    }
}
