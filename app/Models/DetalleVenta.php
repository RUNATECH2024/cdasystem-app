<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    public $timestamps = false;

    protected $table = 'detalle_ventas';

    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'id_venta', 'id_producto', 'cantidad', 'precio_unitario',
    ];

    protected function casts(): array
    {
        return [
            'precio_unitario' => 'decimal:2',
        ];
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function garantias()
    {
        return $this->hasMany(Garantia::class, 'id_detalle_venta', 'id_detalle');
    }
}
