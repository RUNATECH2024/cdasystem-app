<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Subcategoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_returns_only_active_categories(): void
    {
        $activa = Categoria::create(['nombre' => 'Activa', 'orden_visual' => 1, 'activo' => true]);
        Categoria::create(['nombre' => 'Inactiva', 'orden_visual' => 2, 'activo' => false]);
        Subcategoria::create(['id_categoria' => $activa->id_categoria, 'nombre' => 'Sub', 'activo' => true]);

        $response = $this->getJson('/api/catalogo/menu');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['nombre' => 'Activa'])
            ->assertJsonMissing(['nombre' => 'Inactiva']);
    }

    public function test_buscar_filters_by_name_and_ignores_inactive_products(): void
    {
        $categoria = Categoria::create(['nombre' => 'Computadoras', 'activo' => true]);
        $subcategoria = Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'Laptops',
            'activo' => true,
        ]);

        Producto::create([
            'id_subcategoria' => $subcategoria->id_subcategoria,
            'nombre' => 'Laptop Pro',
            'sku' => 'LAP-1',
            'precio_venta' => 100,
            'stock_actual' => 5,
            'activo' => true,
        ]);

        Producto::create([
            'id_subcategoria' => $subcategoria->id_subcategoria,
            'nombre' => 'Laptop Vieja',
            'sku' => 'LAP-2',
            'precio_venta' => 50,
            'stock_actual' => 5,
            'activo' => false,
        ]);

        $response = $this->getJson('/api/catalogo/buscar?q=laptop');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['nombre' => 'Laptop Pro'])
            ->assertJsonMissing(['nombre' => 'Laptop Vieja']);
    }

    public function test_buscar_returns_empty_array_when_query_is_empty(): void
    {
        $this->getJson('/api/catalogo/buscar?q=')
            ->assertOk()
            ->assertExactJson([]);
    }
}
