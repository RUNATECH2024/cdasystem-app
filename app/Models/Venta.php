<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    public $timestamps = false;

    protected $table = 'ventas';

    protected $primaryKey = 'id_venta';

    protected $fillable = [
        'id_cliente', 'id_vendedor', 'fecha', 'fecha_vencimiento',
        'subtotal', 'impuesto', 'total', 'metodo_pago', 'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'fecha_vencimiento' => 'date',
            'subtotal' => 'decimal:2',
            'impuesto' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function vendedor()
    {
        return $this->belongsTo(Usuario::class, 'id_vendedor', 'id_usuario');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'id_venta', 'id_venta');
    }

    public function abonos()
    {
        return $this->hasMany(Abono::class, 'id_venta', 'id_venta');
    }
}
