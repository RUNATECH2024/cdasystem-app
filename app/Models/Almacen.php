<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    public $timestamps = false;

    protected $table = 'almacenes';

    protected $primaryKey = 'id_almacen';

    protected $fillable = [
        'nombre', 'ubicacion', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function inventario()
    {
        return $this->hasMany(InventarioAlmacen::class, 'id_almacen', 'id_almacen');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'id_almacen', 'id_almacen');
    }
}
