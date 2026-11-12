<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleRepuestosOs extends Model
{
    public $timestamps = false;

    protected $table = 'detalle_repuestos_os';

    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'id_orden', 'id_producto', 'cantidad', 'precio_unitario',
    ];

    protected function casts(): array
    {
        return [
            'precio_unitario' => 'decimal:2',
        ];
    }

    public function orden()
    {
        return $this->belongsTo(OrdenServicio::class, 'id_orden', 'id_orden');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
