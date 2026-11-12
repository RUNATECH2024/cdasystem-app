<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    public $timestamps = false;

    protected $table = 'proveedores';

    protected $primaryKey = 'id_proveedor';

    protected $fillable = [
        'ruc', 'nombre', 'telefono', 'email', 'direccion', 'saldo_pendiente', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'saldo_pendiente' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function compras()
    {
        return $this->hasMany(Compra::class, 'id_proveedor', 'id_proveedor');
    }
}
