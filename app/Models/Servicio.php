<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    public $timestamps = false;

    protected $table = 'servicios';

    protected $primaryKey = 'id_servicio';

    protected $fillable = [
        'descripcion', 'precio', 'duracion_estimada',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
        ];
    }
}
