<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Computadoras', 'icono' => 'fa-laptop', 'orden_visual' => 1],
            ['nombre' => 'Componentes', 'icono' => 'fa-microchip', 'orden_visual' => 2],
            ['nombre' => 'Periféricos', 'icono' => 'fa-keyboard', 'orden_visual' => 3],
            ['nombre' => 'Monitores', 'icono' => 'fa-desktop', 'orden_visual' => 4],
            ['nombre' => 'Impresión', 'icono' => 'fa-print', 'orden_visual' => 5],
            ['nombre' => 'Móviles', 'icono' => 'fa-mobile-alt', 'orden_visual' => 6],
            ['nombre' => 'TV & Video', 'icono' => 'fa-tv', 'orden_visual' => 7],
            ['nombre' => 'Audio', 'icono' => 'fa-volume-up', 'orden_visual' => 8],
            ['nombre' => 'Adaptadores & Cables', 'icono' => 'fa-plug', 'orden_visual' => 9],
            ['nombre' => 'Redes', 'icono' => 'fa-network-wired', 'orden_visual' => 10],
            ['nombre' => 'Energía', 'icono' => 'fa-bolt', 'orden_visual' => 11],
            ['nombre' => 'Servidores', 'icono' => 'fa-server', 'orden_visual' => 12],
            ['nombre' => 'Software', 'icono' => 'fa-compact-disc', 'orden_visual' => 13],
            ['nombre' => 'Hogar', 'icono' => 'fa-home', 'orden_visual' => 14],
            ['nombre' => 'Equipos Gaming', 'icono' => 'fa-gamepad', 'orden_visual' => 15],
        ];

        foreach ($categorias as $categoria) {
            Categoria::updateOrCreate(
                ['nombre' => $categoria['nombre']],
                [
                    'icono' => $categoria['icono'],
                    'orden_visual' => $categoria['orden_visual'],
                    'activo' => true,
                ]
            );
        }
    }
}
