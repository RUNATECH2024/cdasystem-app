<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioAlmacen extends Model
{
    public $timestamps = false;

    protected $table = 'inventario_almacen';

    protected $primaryKey = 'id_inventario';

    protected $fillable = [
        'id_producto', 'id_almacen', 'stock',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'id_almacen', 'id_almacen');
    }
}
