<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtributoProducto extends Model
{
    public $timestamps = false;

    protected $table = 'atributos_producto';

    protected $primaryKey = 'id_atributo';

    protected $fillable = [
        'id_producto', 'nombre_atributo', 'valor_atributo',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
