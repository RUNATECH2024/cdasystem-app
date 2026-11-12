<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model
{
    public $timestamps = false;

    protected $table = 'detalle_compras';

    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'id_compra', 'id_producto', 'cantidad', 'precio_costo_unitario',
    ];

    protected function casts(): array
    {
        return [
            'precio_costo_unitario' => 'decimal:2',
        ];
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
