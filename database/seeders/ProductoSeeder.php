<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Subcategoria;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            [
                'subcategoria' => 'LAPTOPS',
                'sku' => 'LAP-001',
                'nombre' => 'Laptop Demo Pro 14"',
                'marca' => 'Lenovo',
                'descripcion_corta' => 'Laptop de demostración para catálogo',
                'descripcion_larga' => 'Equipo portátil de 14 pulgadas con procesador Intel Core i5, 16 GB de RAM y disco SSD de 512 GB. Ideal para trabajo y estudio.',
                'precio_compra' => 1500,
                'precio_venta' => 1899,
                'stock_actual' => 10,
                'garantia_meses' => 12,
                'destacado' => true,
                'atributos' => [
                    'Procesador' => 'Intel Core i5',
                    'Memoria RAM' => '16 GB',
                    'Almacenamiento' => '512 GB SSD',
                    'Pantalla' => '14" Full HD',
                ],
            ],
            [
                'subcategoria' => 'DESKTOP',
                'sku' => 'DES-001',
                'nombre' => 'Desktop Demo Gamer',
                'marca' => 'Genérica',
                'descripcion_corta' => 'Equipo de escritorio para catálogo',
                'descripcion_larga' => 'PC de escritorio orientada a gaming con procesador Ryzen 5, 16 GB de RAM, SSD de 1 TB y tarjeta gráfica dedicada.',
                'precio_compra' => 1200,
                'precio_venta' => 1599,
                'stock_actual' => 8,
                'garantia_meses' => 12,
                'destacado' => true,
                'atributos' => [
                    'Procesador' => 'AMD Ryzen 5',
                    'Memoria RAM' => '16 GB',
                    'Almacenamiento' => '1 TB SSD',
                    'Gráficos' => 'RTX 4060',
                ],
            ],
            [
                'subcategoria' => 'ALL IN ONE',
                'sku' => 'AIO-001',
                'nombre' => 'All in One Demo 24"',
                'marca' => 'HP',
                'descripcion_corta' => 'Equipo todo en uno de demostración',
                'descripcion_larga' => 'Computadora todo en uno con pantalla de 24 pulgadas, procesador Intel Core i5 y almacenamiento SSD. Ahorra espacio en tu escritorio.',
                'precio_compra' => 1000,
                'precio_venta' => 1299,
                'stock_actual' => 6,
                'garantia_meses' => 12,
                'destacado' => false,
                'atributos' => [
                    'Pantalla' => '24" Full HD',
                    'Procesador' => 'Intel Core i5',
                    'Memoria RAM' => '8 GB',
                    'Almacenamiento' => '512 GB SSD',
                ],
            ],
            [
                'subcategoria' => 'IMPRESORAS',
                'sku' => 'IMP-001',
                'nombre' => 'Impresora Láser Demo',
                'marca' => 'Epson',
                'descripcion_corta' => 'Impresora láser de demostración',
                'descripcion_larga' => 'Impresora láser monocromática con conectividad WiFi y Ethernet. Adecuada para oficinas y equipos de trabajo.',
                'precio_compra' => 300,
                'precio_venta' => 450,
                'stock_actual' => 5,
                'garantia_meses' => 12,
                'es_repuesto' => false,
                'atributos' => [
                    'Tecnología' => 'Láser',
                    'Velocidad' => '30 ppm',
                    'Conectividad' => 'WiFi / Ethernet',
                ],
            ],
            [
                'subcategoria' => 'CONSUMIBLES & SUMINISTROS',
                'sku' => 'TON-001',
                'nombre' => 'Tóner Demo Negro',
                'marca' => 'HP',
                'descripcion_corta' => 'Tóner de demostración',
                'descripcion_larga' => 'Cartucho de tóner negro compatible con impresoras láser. Rendimiento aproximado de 3000 páginas.',
                'precio_compra' => 40,
                'precio_venta' => 75,
                'stock_actual' => 20,
                'fecha_vencimiento' => '2027-06-30',
                'es_repuesto' => true,
                'atributos' => [
                    'Rendimiento' => '3000 páginas',
                    'Color' => 'Negro',
                ],
            ],
        ];

        foreach ($productos as $producto) {
            $subcategoria = Subcategoria::where('nombre', $producto['subcategoria'])->first();

            if (! $subcategoria) {
                continue;
            }

            $registro = Producto::firstOrCreate(
                ['sku' => $producto['sku']],
                [
                    'id_subcategoria' => $subcategoria->id_subcategoria,
                    'nombre' => $producto['nombre'],
                    'marca' => $producto['marca'] ?? null,
                    'descripcion_corta' => $producto['descripcion_corta'],
                    'descripcion_larga' => $producto['descripcion_larga'] ?? null,
                    'precio_compra' => $producto['precio_compra'],
                    'precio_venta' => $producto['precio_venta'],
                    'stock_actual' => $producto['stock_actual'],
                    'fecha_vencimiento' => $producto['fecha_vencimiento'] ?? null,
                    'garantia_meses' => $producto['garantia_meses'] ?? 0,
                    'destacado' => $producto['destacado'] ?? false,
                    'es_repuesto' => $producto['es_repuesto'] ?? false,
                    'activo' => true,
                ]
            );

            foreach ($producto['atributos'] ?? [] as $nombre => $valor) {
                $registro->atributos()->updateOrCreate(
                    ['nombre_atributo' => $nombre],
                    ['valor_atributo' => (string) $valor]
                );
            }
        }
    }
}
