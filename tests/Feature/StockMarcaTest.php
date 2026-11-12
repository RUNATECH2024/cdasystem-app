<?php

namespace Tests\Feature;

use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMarcaTest extends TestCase
{
    use RefreshDatabase;

    public function test_marcas_page_groups_active_stock_by_brand(): void
    {
        Producto::create([
            'nombre' => 'Producto HP 1',
            'marca' => 'HP',
            'precio_venta' => 10,
            'stock_actual' => 3,
            'activo' => true,
        ]);

        Producto::create([
            'nombre' => 'Producto HP 2',
            'marca' => 'HP',
            'precio_venta' => 10,
            'stock_actual' => 4,
            'activo' => true,
        ]);

        Producto::create([
            'nombre' => 'Producto sin marca',
            'marca' => null,
            'precio_venta' => 10,
            'stock_actual' => 2,
            'activo' => true,
        ]);

        Producto::create([
            'nombre' => 'Producto inactivo',
            'marca' => 'HP',
            'precio_venta' => 10,
            'stock_actual' => 99,
            'activo' => false,
        ]);

        $response = $this->get(route('marcas'))->assertOk();

        $marcas = $response->viewData('marcas');

        $hp = $marcas->firstWhere('marca', 'HP');
        $this->assertNotNull($hp);
        $this->assertSame(2, $hp['cantidad']);
        $this->assertSame(7, (int) $hp['stock_total']);
    }
}
