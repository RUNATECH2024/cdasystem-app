<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Subcategoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoPageTest extends TestCase
{
    use RefreshDatabase;

    private function crearProducto(array $extra = []): Producto
    {
        $categoria = Categoria::create(['nombre' => 'Computadoras', 'activo' => true]);

        $subcategoria = Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'LAPTOPS',
            'activo' => true,
        ]);

        $producto = Producto::create(array_merge([
            'id_subcategoria' => $subcategoria->id_subcategoria,
            'nombre' => 'Laptop Test',
            'sku' => 'LAP-999',
            'marca' => 'Lenovo',
            'descripcion_corta' => 'Laptop de prueba',
            'descripcion_larga' => 'Descripción detallada de la laptop de prueba.',
            'precio_compra' => 1000,
            'precio_venta' => 1500,
            'stock_actual' => 5,
            'condicion' => 'Nuevo',
            'garantia_meses' => 12,
            'activo' => true,
        ], $extra));

        $producto->atributos()->create([
            'nombre_atributo' => 'Procesador',
            'valor_atributo' => 'Intel Core i5',
        ]);

        return $producto;
    }

    public function test_product_page_shows_details_and_attributes(): void
    {
        $producto = $this->crearProducto();

        $this->get(route('producto.show', $producto->id_producto))
            ->assertOk()
            ->assertSee('Laptop Test')
            ->assertSee('Lenovo')
            ->assertSee('1,500.00')
            ->assertSee('12 meses')
            ->assertSee('Características')
            ->assertSee('Procesador')
            ->assertSee('Intel Core i5');
    }

    public function test_inactive_product_returns_not_found(): void
    {
        $producto = $this->crearProducto(['activo' => false]);

        $this->get(route('producto.show', $producto->id_producto))->assertNotFound();
    }

    public function test_subcategory_listing_links_to_product_page(): void
    {
        $producto = $this->crearProducto();

        $this->get(url('/categoria/'.$producto->id_subcategoria))
            ->assertOk()
            ->assertSee(route('producto.show', $producto->id_producto));
    }

    public function test_home_shows_only_active_featured_products(): void
    {
        $destacado = $this->crearProducto([
            'nombre' => 'Laptop Destacada',
            'sku' => 'LAP-DEST',
            'destacado' => true,
        ]);

        $this->crearProducto([
            'nombre' => 'Laptop Destacada Inactiva',
            'sku' => 'LAP-DEST-OFF',
            'destacado' => true,
            'activo' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Productos destacados')
            ->assertSee('Laptop Destacada')
            ->assertSee(route('producto.show', $destacado->id_producto))
            ->assertDontSee('Laptop Destacada Inactiva');
    }
}
