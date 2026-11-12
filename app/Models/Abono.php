<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abono extends Model
{
    public $timestamps = false;

    protected $table = 'abonos';

    protected $primaryKey = 'id_abono';

    protected $fillable = [
        'id_venta', 'id_compra', 'monto', 'fecha_abono', 'metodo_pago', 'id_usuario',
    ];

    protected function casts(): array
    {
        return [
            'fecha_abono' => 'datetime',
            'monto' => 'decimal:2',
        ];
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
