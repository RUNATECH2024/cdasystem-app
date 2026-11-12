<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Subcategoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
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

        return Producto::create(array_merge([
            'id_subcategoria' => $subcategoria->id_subcategoria,
            'nombre' => 'Laptop SEO',
            'sku' => 'SEO-001',
            'marca' => 'Lenovo',
            'descripcion_corta' => 'Laptop para pruebas de SEO',
            'precio_venta' => 1500,
            'stock_actual' => 5,
            'condicion' => 'Nuevo',
            'activo' => true,
        ], $extra));
    }

    public function test_home_includes_canonical_social_metadata_and_structured_data(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/').'">', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"@type":"SearchAction"', false);
    }

    public function test_product_page_includes_product_structured_data_and_canonical(): void
    {
        $producto = $this->crearProducto();

        $this->get(route('producto.show', $producto->id_producto))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('producto.show', $producto->id_producto).'">', false)
            ->assertSee('property="og:type" content="product"', false)
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"price":"1500.00"', false)
            ->assertSee('"@type":"BreadcrumbList"', false);
    }

    public function test_category_page_includes_collection_structured_data_and_canonical(): void
    {
        $producto = $this->crearProducto();
        $categoria = $producto->subcategoria->categoria;

        $this->get(route('categoria.show', $categoria->id_categoria))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('categoria.show', $categoria->id_categoria).'">', false)
            ->assertSee('"@type":"CollectionPage"', false)
            ->assertSee('"@type":"BreadcrumbList"', false);
    }

    public function test_search_results_are_noindex_but_empty_search_is_indexable(): void
    {
        $this->get(route('buscar', ['q' => 'laptop']))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);

        $this->get(route('buscar'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="index, follow">', false);
    }

    public function test_sitemap_lists_active_categories_subcategories_and_products(): void
    {
        $producto = $this->crearProducto();
        $categoria = $producto->subcategoria->categoria;

        $inactivo = $this->crearProducto([
            'nombre' => 'Laptop SEO inactiva',
            'sku' => 'SEO-002',
            'activo' => false,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk()->assertHeader('Content-Type', 'application/xml');

        $contenido = $response->getContent();

        $this->assertStringContainsString('<loc>'.route('categoria.show', $categoria->id_categoria).'</loc>', $contenido);
        $this->assertStringContainsString('<loc>'.url('/categoria/'.$producto->id_subcategoria).'</loc>', $contenido);
        $this->assertStringContainsString('<loc>'.route('producto.show', $producto->id_producto).'</loc>', $contenido);
        $this->assertStringNotContainsString(route('producto.show', $inactivo->id_producto), $contenido);
    }

    public function test_robots_points_to_the_sitemap_and_blocks_admin(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: '.url('/sitemap.xml'));
    }
}
