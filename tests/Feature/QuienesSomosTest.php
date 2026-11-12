<?php

namespace Tests\Feature;

use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuienesSomosTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_shows_cda_system_identity(): void
    {
        $this->get(route('quienes-somos'))
            ->assertOk()
            ->assertSee('Quiénes Somos')
            ->assertSee('CDA System C.A.')
            ->assertSee('Misión')
            ->assertSee('Visión')
            ->assertSee('Valores')
            ->assertSee('098 082 1599')
            ->assertSee('https://wa.me/593980821599')
            ->assertSee('https://quasad.tech/')
            ->assertSee(route('quienes-somos.repuestos'))
            ->assertDontSee('(02) 222-8218')
            ->assertDontSee('TecnoMega')
            ->assertDontSee('Tecnomega');
    }

    public function test_home_links_to_about_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('quienes-somos'));
    }

    public function test_spare_parts_page_lists_only_active_spare_parts(): void
    {
        $repuesto = Producto::create([
            'nombre' => 'Ribbon Mask Epson LX350',
            'sku' => '1574516',
            'marca' => 'EPSON',
            'precio_venta' => 2.44,
            'stock_actual' => 4,
            'es_repuesto' => true,
            'activo' => true,
        ]);
        Producto::create([
            'nombre' => 'Laptop no es repuesto',
            'sku' => 'LAP-001',
            'marca' => 'Lenovo',
            'precio_venta' => 500,
            'stock_actual' => 2,
            'es_repuesto' => false,
            'activo' => true,
        ]);
        Producto::create([
            'nombre' => 'Repuesto inactivo',
            'sku' => 'REP-002',
            'marca' => 'EPSON',
            'precio_venta' => 10,
            'stock_actual' => 1,
            'es_repuesto' => true,
            'activo' => false,
        ]);

        $this->get(route('quienes-somos.repuestos'))
            ->assertOk()
            ->assertSee('Ribbon Mask Epson LX350')
            ->assertSee(route('producto.show', $repuesto->id_producto))
            ->assertDontSee('Laptop no es repuesto')
            ->assertDontSee('Repuesto inactivo');
    }
}
