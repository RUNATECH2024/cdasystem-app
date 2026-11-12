<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcategoria extends Model
{
    public $timestamps = false;

    protected $table = 'subcategorias';

    protected $primaryKey = 'id_subcategoria';

    protected $fillable = ['id_categoria', 'nombre', 'imagen_portada', 'orden_visual', 'activo'];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_subcategoria', 'id_subcategoria');
    }
}
