<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Subcategoria;
use Illuminate\Database\Seeder;

class SubcategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $mapa = [
            'Computadoras' => [
                'LAPTOPS',
                'DESKTOP',
                'ALL IN ONE',
                'WORKSTATION',
                'POS',
                'MINI PC',
                'ACCESORIOS DE COMPUTADORES',
            ],
            'Componentes' => [
                'MOTHERBOARD',
                'PROCESADORES',
                'TARJETAS DE VIDEO',
                'FUENTES DE PODER',
                'SISTEMAS DE ENFRIAMIENTO',
                'DISCOS DUROS',
                'MEMORIAS RAM',
                'CASES',
                'AUDIO',
                'REDES',
                'ADAPTADORES & CABLES',
            ],
            'Periféricos' => [
                'CAMARAS WEB',
                'CAMARAS DE VIDEO VIGILANCIA',
                'CAMARAS DIGITALES',
                'CAMARAS DE VIDEO CONFERENCIA',
                'EXPANSION & CONECTIVIDAD',
                'PROTECCION Y TRANSPORTE',
                'MOUSE',
                'FLASH MEMORY / MICRO SD',
                'ACCESORIOS PUNTO DE VENTA',
                'PUNTEROS',
                'STREAMING',
                'TECLADOS',
            ],
            'Monitores' => [
                'COMERCIALES',
                'DIAGNOSTICO MEDICO',
                'DISEÑO/FOTOGRAFIA',
                'GAMING',
                'INTELIGENTES',
                'OFICINA/HOGAR',
                'PANEL LED',
                'PORTABLES',
                'TOUCH',
            ],
            'Impresión' => [
                'ESCANERS',
                'ACCESORIOS',
                'IMPRESORAS',
                'CONSUMIBLES & SUMINISTROS',
            ],
            'Móviles' => [
                'CELULARES',
                'TABLETS',
                'ACCESORIOS',
            ],
            'TV & Video' => [
                'TELEVISORES',
                'PROYECTORES',
                'ACCESORIOS DE TV & VIDEO',
            ],
            'Audio' => [
                'HEADSET',
                'AURICULARES',
                'PARLANTES',
                'MICROFONOS',
                'ACCESORIOS DE AUDIO',
            ],
            'Adaptadores & Cables' => [
                'CABLES',
                'ADAPTADORES',
            ],
            'Redes' => [
                'ROUTERS',
                'EQUIPOS NAS',
                'ACCESS POINT',
                'SWITCHES',
                'TARJETAS DE RED',
                'ANTENAS',
                'ADAPTADORES',
                'ACCESORIOS',
                'WIFI DECO',
            ],
            'Energía' => [
                'GENERADORES ELECTRICOS',
                'UPS',
                'ENERGIA SOLAR',
                'ACCESORIOS UPS',
                'UPS ONLINE',
                'POWER STATION',
                'SUPRESORES DE PICOS',
                'REGULADORES DE VOLTAJE',
            ],
            'Servidores' => [
                'RACK',
                'TORRE ML',
                'TORRE',
                'COMPONENTES DE SERVIDOR',
                'ACCESORIOS PARA SERVIDOR',
            ],
            'Software' => [
                'ANTIVIRUS',
                'MICROSOFT',
                'PROGRAMAS',
            ],
            'Hogar' => [
                'ELECTRODOMESTICOS',
                'JUGUETES',
                'SMART WATCH',
                'DOMOTICA',
            ],
            'Equipos Gaming' => [
                'CONSOLA',
                'CONTROLES GAMING',
                'SILLAS GAMER',
            ],
        ];

        $this->renombrarSubcategoriasAntiguas();

        foreach ($mapa as $nombreCategoria => $subcategorias) {
            $categoria = Categoria::where('nombre', $nombreCategoria)->first();

            if (! $categoria) {
                continue;
            }

            foreach ($subcategorias as $orden => $nombreSubcategoria) {
                Subcategoria::updateOrCreate(
                    ['id_categoria' => $categoria->id_categoria, 'nombre' => $nombreSubcategoria],
                    ['orden_visual' => $orden + 1, 'activo' => true]
                );
            }
        }
    }

    /**
     * Renombra las subcategorías antiguas de Impresión para conservar sus productos.
     */
    private function renombrarSubcategoriasAntiguas(): void
    {
        $categoria = Categoria::where('nombre', 'Impresión')->first();

        if (! $categoria) {
            return;
        }

        $renombres = [
            'IMPRESORAS LÁSER' => 'IMPRESORAS',
            'TÓNER Y TINTAS' => 'CONSUMIBLES & SUMINISTROS',
        ];

        foreach ($renombres as $anterior => $nuevo) {
            Subcategoria::where('id_categoria', $categoria->id_categoria)
                ->where('nombre', $anterior)
                ->update(['nombre' => $nuevo]);
        }
    }
}
