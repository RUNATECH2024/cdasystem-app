<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    public $timestamps = false;

    protected $table = 'compras';

    protected $primaryKey = 'id_compra';

    protected $fillable = [
        'id_proveedor', 'id_usuario', 'numero_factura', 'fecha_compra',
        'fecha_vencimiento', 'subtotal', 'impuesto', 'total', 'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_compra' => 'datetime',
            'fecha_vencimiento' => 'date',
            'subtotal' => 'decimal:2',
            'impuesto' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'id_compra', 'id_compra');
    }

    public function abonos()
    {
        return $this->hasMany(Abono::class, 'id_compra', 'id_compra');
    }
}
