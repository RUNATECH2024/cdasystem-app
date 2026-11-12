<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Cliente extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'clientes';

    protected $primaryKey = 'id_cliente';

    public $timestamps = false;

    protected $fillable = [
        'dni_ruc', 'nombre', 'telefono', 'email', 'direccion', 'password', 'saldo_pendiente', 'activo',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'saldo_pendiente' => 'decimal:2',
            'fecha_registro' => 'datetime',
        ];
    }

    public function equipos(): HasMany
    {
        return $this->hasMany(EquipoCliente::class, 'id_cliente', 'id_cliente');
    }

    public function ordenes(): HasMany
    {
        return $this->hasMany(OrdenServicio::class, 'id_cliente', 'id_cliente');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(PedidoWeb::class, 'id_cliente', 'id_cliente');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_cliente', 'id_cliente');
    }
}
