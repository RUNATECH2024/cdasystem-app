<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Subcategoria;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminReporteTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdmin(): Usuario
    {
        return Usuario::create([
            'nombre' => 'Administrador',
            'usuario' => 'admin',
            'password_hash' => Hash::make('admin123'),
            'rol' => 'Admin',
            'activo' => true,
        ]);
    }

    private function crearDatos(): array
    {
        $categoria = Categoria::create(['nombre' => 'Computadoras', 'activo' => true]);

        $subcategoria = Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'LAPTOPS',
            'activo' => true,
        ]);

        $producto = Producto::create([
            'id_subcategoria' => $subcategoria->id_subcategoria,
            'nombre' => 'Laptop Reporte',
            'sku' => 'REP-001',
            'marca' => 'Lenovo',
            'precio_compra' => 100,
            'precio_venta' => 150,
            'stock_actual' => 2,
            'stock_minimo' => 5,
            'activo' => true,
        ]);

        $cliente = Cliente::create([
            'nombre' => 'Cliente Reporte',
            'dni_ruc' => '12345678',
            'telefono' => '555-1000',
            'email' => 'cliente-reporte@example.test',
            'saldo_pendiente' => 25,
            'activo' => true,
        ]);

        $venta = Venta::create([
            'id_cliente' => $cliente->id_cliente,
            'fecha' => now(),
            'subtotal' => 150,
            'impuesto' => 0,
            'total' => 150,
            'metodo_pago' => 'Efectivo',
            'estado' => 'Pagada',
        ]);

        DetalleVenta::create([
            'id_venta' => $venta->id_venta,
            'id_producto' => $producto->id_producto,
            'cantidad' => 1,
            'precio_unitario' => 150,
        ]);

        $proveedor = Proveedor::create([
            'ruc' => '20111111111',
            'nombre' => 'Proveedor Reporte',
            'telefono' => '555-2000',
            'email' => 'proveedor-reporte@example.test',
            'saldo_pendiente' => 40,
            'activo' => true,
        ]);

        Compra::create([
            'id_proveedor' => $proveedor->id_proveedor,
            'numero_factura' => 'REP-COMPRA-1',
            'fecha_compra' => now(),
            'subtotal' => 300,
            'impuesto' => 0,
            'total' => 300,
            'estado' => 'Pendiente',
        ]);

        return [$categoria, $subcategoria, $producto, $venta, $cliente, $proveedor];
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin/reportes')->assertRedirect('/admin/login');
    }

    public function test_admin_can_see_reports_with_inventory_and_sales(): void
    {
        $admin = $this->crearAdmin();
        $this->crearDatos();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.reportes.index'))
            ->assertOk()
            ->assertSee('Reportes')
            ->assertSee('Stock por categoría')
            ->assertSee('Computadoras')
            ->assertSee('Laptop Reporte')
            ->assertSee('Ventas por día')
            ->assertSee('Ventas por método de pago')
            ->assertSee('Top productos vendidos')
            ->assertSee('Efectivo')
            ->assertSee('150.00')
            ->assertSee('Clientes por venta en el periodo')
            ->assertSee('Cliente Reporte')
            ->assertSee('Proveedores por compra en el periodo')
            ->assertSee('Proveedor Reporte')
            ->assertSee('300.00');
    }

    public function test_admin_can_export_inventory_csv(): void
    {
        $admin = $this->crearAdmin();
        $this->crearDatos();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.reportes.exportar', ['tipo' => 'inventario']));

        $response->assertOk();

        $contenido = $response->streamedContent();

        $this->assertStringContainsString('Producto', $contenido);
        $this->assertStringContainsString('Laptop Reporte', $contenido);
        $this->assertStringContainsString('Computadoras', $contenido);
    }

    public function test_admin_can_export_sales_csv(): void
    {
        $admin = $this->crearAdmin();
        $this->crearDatos();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.reportes.exportar', [
                'tipo' => 'ventas',
                'desde' => now()->subDay()->toDateString(),
                'hasta' => now()->addDay()->toDateString(),
            ]));

        $response->assertOk();

        $contenido = $response->streamedContent();

        $this->assertStringContainsString('Metodo pago', $contenido);
        $this->assertStringContainsString('Efectivo', $contenido);
        $this->assertStringContainsString('150.00', $contenido);
    }

    public function test_admin_can_export_clients_csv(): void
    {
        $admin = $this->crearAdmin();
        $this->crearDatos();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.reportes.exportar', [
                'tipo' => 'clientes',
                'desde' => now()->subDay()->toDateString(),
                'hasta' => now()->addDay()->toDateString(),
            ]));

        $response->assertOk();

        $contenido = $response->streamedContent();

        $this->assertStringContainsString('Cliente', $contenido);
        $this->assertStringContainsString('Cliente Reporte', $contenido);
        $this->assertStringContainsString('150.00', $contenido);
        $this->assertStringContainsString('25.00', $contenido);
    }

    public function test_admin_can_export_suppliers_csv(): void
    {
        $admin = $this->crearAdmin();
        $this->crearDatos();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.reportes.exportar', [
                'tipo' => 'proveedores',
                'desde' => now()->subDay()->toDateString(),
                'hasta' => now()->addDay()->toDateString(),
            ]));

        $response->assertOk();

        $contenido = $response->streamedContent();

        $this->assertStringContainsString('Proveedor', $contenido);
        $this->assertStringContainsString('Proveedor Reporte', $contenido);
        $this->assertStringContainsString('300.00', $contenido);
        $this->assertStringContainsString('40.00', $contenido);
    }

    public function test_admin_can_export_client_and_supplier_reports_pdf(): void
    {
        $admin = $this->crearAdmin();
        $this->crearDatos();

        $parametros = [
            'desde' => now()->subDay()->toDateString(),
            'hasta' => now()->addDay()->toDateString(),
        ];

        $clientes = $this->actingAs($admin, 'admin')
            ->get(route('admin.reportes.pdf', ['tipo' => 'clientes'] + $parametros));
        $proveedores = $this->actingAs($admin, 'admin')
            ->get(route('admin.reportes.pdf', ['tipo' => 'proveedores'] + $parametros));

        $clientes->assertOk();
        $proveedores->assertOk();
        $this->assertStringContainsString('application/pdf', $clientes->headers->get('content-type'));
        $this->assertStringContainsString('application/pdf', $proveedores->headers->get('content-type'));
    }

    public function test_unknown_export_type_returns_not_found(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.reportes.exportar', ['tipo' => 'otro']))
            ->assertNotFound();
    }
}
