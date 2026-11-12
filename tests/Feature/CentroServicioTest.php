<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CentroServicioTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_center_page_shows_warranty_sections_and_contact_details(): void
    {
        $this->get(route('centro-servicio'))
            ->assertOk()
            ->assertSee('Centro de Servicio CDA System')
            ->assertSee('Principales categorías de repuestos')
            ->assertSee('Políticas de garantías')
            ->assertSee('Estado de garantías')
            ->assertSee('Solicitud de garantías')
            ->assertSee('Servicio Técnico')
            ->assertSee('Gral. Murgeon OE3-254 & Av. América')
            ->assertSee('Lunes a Viernes: 8:30 - 18:00')
            ->assertSee('(02) 2908202')
            ->assertSee('garantias@tecnomega.com');
    }

    public function test_service_center_links_principal_categories_and_featured_products(): void
    {
        $categoria = Categoria::create(['nombre' => 'Computadoras', 'activo' => true]);

        $destacado = Producto::create([
            'nombre' => 'Laptop para servicio',
            'sku' => 'SRV-001',
            'marca' => 'Dell',
            'precio_venta' => 800,
            'stock_actual' => 3,
            'destacado' => true,
            'activo' => true,
        ]);

        Producto::create([
            'nombre' => 'Producto destacado inactivo',
            'sku' => 'SRV-002',
            'marca' => 'Dell',
            'precio_venta' => 900,
            'stock_actual' => 1,
            'destacado' => true,
            'activo' => false,
        ]);

        $this->get(route('centro-servicio'))
            ->assertOk()
            ->assertSee('Computador')
            ->assertSee(route('categoria.show', $categoria->id_categoria))
            ->assertSee('Laptop para servicio')
            ->assertSee(route('producto.show', $destacado->id_producto))
            ->assertDontSee('Producto destacado inactivo');
    }

    public function test_about_page_links_to_service_center_page(): void
    {
        $this->get(route('quienes-somos'))
            ->assertOk()
            ->assertSee(route('centro-servicio'));
    }
}
