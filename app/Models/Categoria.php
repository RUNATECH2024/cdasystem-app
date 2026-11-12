<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Categoria extends Model
{
    private const IMAGENES = [
        'computadoras' => 'computadores.webp',
        'componentes' => 'componentes.webp',
        'perifericos' => 'perifericos.webp',
        'monitores' => 'monitores.webp',
        'impresion' => 'impresoras-suministros.webp',
        'moviles' => 'celulares-tablets.webp',
        'tv & video' => 'tv-video.webp',
        'audio' => 'audio.webp',
        'adaptadores & cables' => 'accesorios-cables.webp',
        'redes' => 'redes.webp',
        'energia' => 'proteccion-electrica.webp',
        'servidores' => 'servidores.webp',
        'software' => 'software.webp',
        'hogar' => 'hogar.webp',
        'equipos gaming' => 'equipos-gaming.webp',
    ];

    public $timestamps = false;

    protected $table = 'categorias';

    protected $primaryKey = 'id_categoria';

    protected $fillable = ['nombre', 'icono', 'orden_visual', 'activo'];

    public function getImagenAttribute(): ?string
    {
        $clave = strtolower(Str::ascii((string) $this->nombre));

        return self::IMAGENES[$clave] ?? null;
    }

    public function subcategorias()
    {
        return $this->hasMany(Subcategoria::class, 'id_categoria', 'id_categoria');
    }
}
