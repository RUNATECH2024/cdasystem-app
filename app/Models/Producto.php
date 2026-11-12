<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Producto extends Model
{
    use HasFactory;

    // Desactivamos los timestamps automáticos (created_at, updated_at)
    public $timestamps = false;

    protected $table = 'productos';

    protected $primaryKey = 'id_producto';

    // Agregamos 'condicion' y 'garantia_meses' para que Laravel los guarde
    protected $fillable = [
        'id_subcategoria', 'codigo_barras', 'sku', 'nombre', 'marca',
        'descripcion_corta', 'descripcion_larga', 'precio_compra',
        'precio_venta', 'stock_actual', 'stock_minimo', 'fecha_vencimiento',
        'imagen_principal', 'imagenes_adicionales', 'destacado', 'es_repuesto', 'activo',
        'condicion', 'garantia_meses',
    ];

    protected function casts(): array
    {
        return [
            'precio_compra' => 'decimal:2',
            'precio_venta' => 'decimal:2',
            'destacado' => 'boolean',
            'es_repuesto' => 'boolean',
            'imagenes_adicionales' => 'array',
            'activo' => 'boolean',
            'fecha_vencimiento' => 'date',
            'garantia_meses' => 'integer',
        ];
    }

    public static function generarCodigoRepuesto(?string $marca, ?string $sku): ?string
    {
        $marcaNormalizada = Str::upper(Str::substr(
            preg_replace('/[^A-Za-z0-9]/', '', Str::ascii((string) $marca)),
            0,
            3
        ));
        $skuNormalizado = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', (string) $sku));

        if ($marcaNormalizada === '' || $skuNormalizado === '') {
            return null;
        }

        return 'REP'.$marcaNormalizada.$skuNormalizado;
    }

    // Relaciones
    public function subcategoria()
    {
        return $this->belongsTo(Subcategoria::class, 'id_subcategoria', 'id_subcategoria');
    }

    public function atributos()
    {
        return $this->hasMany(AtributoProducto::class, 'id_producto', 'id_producto');
    }
}
