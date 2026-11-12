<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoCliente extends Model
{
    public $timestamps = false;

    protected $table = 'equipos_cliente';

    protected $primaryKey = 'id_equipo';

    protected $fillable = [
        'id_cliente', 'tipo_equipo', 'marca', 'modelo', 'numero_serie',
        'contador_paginas', 'observaciones',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function ordenes()
    {
        return $this->hasMany(OrdenServicio::class, 'id_equipo', 'id_equipo');
    }
}
