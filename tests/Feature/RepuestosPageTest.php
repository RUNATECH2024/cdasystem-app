<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Subcategoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RepuestosPageTest extends TestCase
{
    use RefreshDatabase;

    private function crearRepuesto(array $extra = []): Producto
    {
        return Producto::create(array_merge([
            'nombre' => 'Repuesto de prueba',
            'sku' => 'REP-'.Str::upper(Str::random(8)),
            'marca' => 'EPSON',
            'precio_venta' => 5,
            'stock_actual' => 2,
            'es_repuesto' => true,
            'activo' => true,
        ], $extra));
    }

    public function test_repuestos_page_searches_by_name_sku_or_brand(): void
    {
        $this->crearRepuesto([
            'nombre' => 'Ribbon Mask Epson LX350',
            'sku' => 'RIB-001',
            'marca' => 'EPSON',
        ]);
        $this->crearRepuesto([
            'nombre' => 'Toner HP Negro',
            'sku' => 'TON-001',
            'marca' => 'HP',
        ]);

        $this->get(route('quienes-somos.repuestos', ['q' => 'ribbon']))
            ->assertOk()
            ->assertSee('Ribbon Mask Epson LX350')
            ->assertDontSee('Toner HP Negro');

        $this->get(route('quienes-somos.repuestos', ['q' => 'TON-001']))
            ->assertOk()
            ->assertSee('Toner HP Negro')
            ->assertDontSee('Ribbon Mask Epson LX350');

        $this->get(route('quienes-somos.repuestos', ['q' => 'HP']))
            ->assertOk()
            ->assertSee('Toner HP Negro')
            ->assertDontSee('Ribbon Mask Epson LX350');
    }

    public function test_repuestos_page_filters_by_category(): void
    {
        $categoria = Categoria::create(['nombre' => 'Impresión', 'activo' => true]);
        $subcategoria = Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'IMPRESORAS',
            'activo' => true,
        ]);

        $this->crearRepuesto([
            'nombre' => 'Repuesto de impresora',
            'id_subcategoria' => $subcategoria->id_subcategoria,
        ]);
        $this->crearRepuesto(['nombre' => 'Repuesto sin categoría']);

        $this->get(route('quienes-somos.repuestos', ['categoria' => $categoria->id_categoria]))
            ->assertOk()
            ->assertSee('Repuesto de impresora')
            ->assertDontSee('Repuesto sin categoría');
    }

    public function test_repuestos_page_sorts_by_price(): void
    {
        $this->crearRepuesto(['nombre' => 'Repuesto económico', 'precio_venta' => 2.44]);
        $this->crearRepuesto(['nombre' => 'Repuesto costoso', 'precio_venta' => 30]);

        $this->get(route('quienes-somos.repuestos'))
            ->assertOk()
            ->assertSeeInOrder(['Repuesto económico', 'Repuesto costoso']);

        $this->get(route('quienes-somos.repuestos', ['orden' => 'mayor-precio']))
            ->assertOk()
            ->assertSeeInOrder(['Repuesto costoso', 'Repuesto económico']);
    }

    public function test_repuestos_page_shows_purchase_form_for_available_stock(): void
    {
        $repuesto = $this->crearRepuesto(['nombre' => 'Ribbon Mask DISPONIBLE']);

        $this->get(route('quienes-somos.repuestos'))
            ->assertOk()
            ->assertSee(route('carrito.add', $repuesto->id_producto))
            ->assertSee('Comprar');
    }
}
