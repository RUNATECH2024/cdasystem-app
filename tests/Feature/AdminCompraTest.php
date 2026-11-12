<?php

namespace Tests\Feature;

use App\Models\Abono;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCompraTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdmin(): Usuario
    {
        return Usuario::create([
            'nombre' => 'Administrador',
            'usuario' => 'admin',
            'password_hash' => Hash::make('contrasena-segura-123'),
            'rol' => 'Admin',
            'activo' => true,
        ]);
    }

    private function crearProveedor(string $nombre = 'Proveedor de Repuestos'): Proveedor
    {
        return Proveedor::create([
            'ruc' => '209'.random_int(10000000, 99999999),
            'nombre' => $nombre,
            'telefono' => '555-9000',
            'email' => strtolower(str_replace(' ', '.', $nombre)).'@example.test',
            'activo' => true,
        ]);
    }

    private function crearProducto(string $nombre = 'Filtro de aceite', int $stock = 5, float $costo = 10): Producto
    {
        return Producto::create([
            'sku' => 'SKU-'.random_int(1000, 9999),
            'nombre' => $nombre,
            'precio_compra' => $costo,
            'precio_venta' => $costo * 2,
            'stock_actual' => $stock,
            'activo' => true,
        ]);
    }

    public function test_guest_cannot_access_purchase_management(): void
    {
        $compra = Compra::create([
            'id_proveedor' => $this->crearProveedor()->id_proveedor,
            'fecha_compra' => now(),
            'subtotal' => 100,
            'impuesto' => 0,
            'total' => 100,
            'estado' => 'Pendiente',
        ]);

        $this->get(route('admin.compras.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.compras.create'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.compras.show', $compra))->assertRedirect(route('admin.login'));
        $this->get(route('admin.compras.pdf', $compra))->assertRedirect(route('admin.login'));
        $this->post(route('admin.compras.store'), [])->assertRedirect(route('admin.login'));
        $this->post(route('admin.compras.abonos.store', $compra), [])->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_purchase_list_create_form_and_details(): void
    {
        $admin = $this->crearAdmin();
        $proveedor = $this->crearProveedor('Proveedor Visible');
        $producto = $this->crearProducto();
        $compra = Compra::create([
            'id_proveedor' => $proveedor->id_proveedor,
            'id_usuario' => $admin->id_usuario,
            'numero_factura' => 'FAC-VISIBLE',
            'fecha_compra' => now(),
            'subtotal' => 100,
            'impuesto' => 12,
            'total' => 112,
            'estado' => 'Pendiente',
        ]);
        DetalleCompra::create([
            'id_compra' => $compra->id_compra,
            'id_producto' => $producto->id_producto,
            'cantidad' => 2,
            'precio_costo_unitario' => 50,
        ]);
        Abono::create([
            'id_compra' => $compra->id_compra,
            'monto' => 12,
            'fecha_abono' => now(),
            'metodo_pago' => 'Efectivo',
            'id_usuario' => $admin->id_usuario,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.compras.index'))
            ->assertOk()
            ->assertSee('Compras')
            ->assertSee('Proveedor Visible')
            ->assertSee('FAC-VISIBLE');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.compras.create', ['proveedor' => $proveedor->id_proveedor]))
            ->assertOk()
            ->assertSee('Nueva compra')
            ->assertSee('Proveedor Visible')
            ->assertSee('value="'.$proveedor->id_proveedor.'" selected', false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.compras.show', $compra))
            ->assertOk()
            ->assertSee('Compra #'.$compra->id_compra)
            ->assertSee('FAC-VISIBLE')
            ->assertSee('Proveedor Visible')
            ->assertSee('Filtro de aceite')
            ->assertSee('$112.00')
            ->assertSee('$100.00');
    }

    public function test_admin_can_register_a_purchase_with_initial_payment_and_stock_update(): void
    {
        $admin = $this->crearAdmin();
        $proveedor = $this->crearProveedor();
        $productoA = $this->crearProducto('Filtro de aire', 5, 8);
        $productoB = $this->crearProducto('Bujía iridio', 10, 4);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.compras.store'), [
                'id_proveedor' => $proveedor->id_proveedor,
                'numero_factura' => 'FAC-100',
                'fecha_compra' => '2026-11-10',
                'fecha_vencimiento' => '2026-12-10',
                'impuesto' => 12,
                'monto_pagado' => 20,
                'metodo_pago' => 'Transferencia',
                'productos' => [
                    ['id_producto' => $productoA->id_producto, 'cantidad' => 3, 'precio_costo_unitario' => 12.50],
                    ['id_producto' => $productoB->id_producto, 'cantidad' => 2, 'precio_costo_unitario' => 5],
                ],
            ]);

        $compra = Compra::firstOrFail();

        $response
            ->assertRedirect(route('admin.compras.show', $compra))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('compras', [
            'id_compra' => $compra->id_compra,
            'id_proveedor' => $proveedor->id_proveedor,
            'id_usuario' => $admin->id_usuario,
            'numero_factura' => 'FAC-100',
            'subtotal' => 47.50,
            'impuesto' => 12,
            'total' => 59.50,
            'estado' => 'Parcial',
        ]);
        $this->assertDatabaseCount('detalle_compras', 2);
        $this->assertDatabaseHas('detalle_compras', [
            'id_compra' => $compra->id_compra,
            'id_producto' => $productoA->id_producto,
            'cantidad' => 3,
            'precio_costo_unitario' => 12.50,
        ]);

        $this->assertDatabaseHas('productos', [
            'id_producto' => $productoA->id_producto,
            'stock_actual' => 8,
            'precio_compra' => 12.50,
        ]);
        $this->assertDatabaseHas('productos', [
            'id_producto' => $productoB->id_producto,
            'stock_actual' => 12,
            'precio_compra' => 5,
        ]);

        $this->assertDatabaseHas('movimientos_inventario', [
            'id_producto' => $productoA->id_producto,
            'tipo_movimiento' => 'ENTRADA',
            'origen' => 'COMPRA',
            'cantidad' => 3,
            'stock_anterior' => 5,
            'stock_nuevo' => 8,
            'id_referencia' => $compra->id_compra,
            'id_usuario' => $admin->id_usuario,
        ]);
        $this->assertDatabaseCount('movimientos_inventario', 2);

        $this->assertDatabaseHas('abonos', [
            'id_compra' => $compra->id_compra,
            'monto' => 20,
            'metodo_pago' => 'Transferencia',
            'id_usuario' => $admin->id_usuario,
        ]);
        $this->assertDatabaseHas('proveedores', [
            'id_proveedor' => $proveedor->id_proveedor,
            'saldo_pendiente' => 39.50,
        ]);
    }

    public function test_purchase_without_payment_is_pending_and_adds_full_total_to_supplier_balance(): void
    {
        $admin = $this->crearAdmin();
        $proveedor = $this->crearProveedor();
        $producto = $this->crearProducto();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.compras.store'), [
                'id_proveedor' => $proveedor->id_proveedor,
                'fecha_compra' => now()->toDateString(),
                'impuesto' => 5,
                'productos' => [
                    ['id_producto' => $producto->id_producto, 'cantidad' => 2, 'precio_costo_unitario' => 25],
                ],
            ])
            ->assertSessionHasNoErrors();

        $compra = Compra::firstOrFail();

        $this->assertSame('Pendiente', $compra->estado);
        $this->assertSame('55.00', $compra->total);
        $this->assertDatabaseCount('abonos', 0);
        $this->assertDatabaseHas('proveedores', [
            'id_proveedor' => $proveedor->id_proveedor,
            'saldo_pendiente' => 55,
        ]);
    }

    public function test_purchase_paid_in_full_is_marked_as_paid_without_pending_balance(): void
    {
        $admin = $this->crearAdmin();
        $proveedor = $this->crearProveedor();
        $producto = $this->crearProducto();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.compras.store'), [
                'id_proveedor' => $proveedor->id_proveedor,
                'fecha_compra' => now()->toDateString(),
                'impuesto' => 12,
                'monto_pagado' => 112,
                'productos' => [
                    ['id_producto' => $producto->id_producto, 'cantidad' => 1, 'precio_costo_unitario' => 100],
                ],
            ])
            ->assertSessionHasNoErrors();

        $compra = Compra::firstOrFail();

        $this->assertSame('Pagada', $compra->estado);
        $this->assertDatabaseHas('abonos', ['id_compra' => $compra->id_compra, 'monto' => 112]);
        $this->assertDatabaseHas('proveedores', [
            'id_proveedor' => $proveedor->id_proveedor,
            'saldo_pendiente' => 0,
        ]);
    }

    public function test_purchase_payload_is_validated(): void
    {
        $admin = $this->crearAdmin();
        $proveedor = $this->crearProveedor();
        $producto = $this->crearProducto();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.compras.store'), [
                'id_proveedor' => 999999,
                'fecha_compra' => 'fecha-invalida',
                'productos' => [],
            ])
            ->assertSessionHasErrors(['id_proveedor', 'fecha_compra', 'productos']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.compras.store'), [
                'id_proveedor' => $proveedor->id_proveedor,
                'fecha_compra' => now()->toDateString(),
                'productos' => [
                    ['id_producto' => $producto->id_producto, 'cantidad' => 1, 'precio_costo_unitario' => 10],
                    ['id_producto' => $producto->id_producto, 'cantidad' => 0, 'precio_costo_unitario' => -5],
                ],
            ])
            ->assertSessionHasErrors(['productos.1.id_producto', 'productos.1.cantidad', 'productos.1.precio_costo_unitario']);

        $this->assertDatabaseCount('compras', 0);
        $this->assertDatabaseHas('productos', ['id_producto' => $producto->id_producto, 'stock_actual' => 5]);
    }

    public function test_initial_payment_cannot_exceed_purchase_total(): void
    {
        $admin = $this->crearAdmin();
        $proveedor = $this->crearProveedor();
        $producto = $this->crearProducto();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.compras.store'), [
                'id_proveedor' => $proveedor->id_proveedor,
                'fecha_compra' => now()->toDateString(),
                'monto_pagado' => 101,
                'productos' => [
                    ['id_producto' => $producto->id_producto, 'cantidad' => 1, 'precio_costo_unitario' => 100],
                ],
            ])
            ->assertSessionHasErrors('monto_pagado');

        $this->assertDatabaseCount('compras', 0);
        $this->assertDatabaseHas('productos', ['id_producto' => $producto->id_producto, 'stock_actual' => 5]);
        $this->assertDatabaseHas('proveedores', ['id_proveedor' => $proveedor->id_proveedor, 'saldo_pendiente' => 0]);
    }

    public function test_admin_can_register_an_abono_and_purchase_becomes_paid(): void
    {
        $admin = $this->crearAdmin();
        $proveedor = $this->crearProveedor();
        $proveedor->update(['saldo_pendiente' => 100]);
        $compra = Compra::create([
            'id_proveedor' => $proveedor->id_proveedor,
            'fecha_compra' => now(),
            'subtotal' => 100,
            'impuesto' => 0,
            'total' => 100,
            'estado' => 'Pendiente',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.compras.abonos.store', $compra), [
                'monto' => 40,
                'metodo_pago' => 'Efectivo',
                'fecha_abono' => now()->toDateString(),
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('abonos', [
            'id_compra' => $compra->id_compra,
            'monto' => 40,
            'metodo_pago' => 'Efectivo',
            'id_usuario' => $admin->id_usuario,
        ]);
        $this->assertDatabaseHas('compras', ['id_compra' => $compra->id_compra, 'estado' => 'Parcial']);
        $this->assertDatabaseHas('proveedores', ['id_proveedor' => $proveedor->id_proveedor, 'saldo_pendiente' => 60]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.compras.abonos.store', $compra), ['monto' => 60])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('compras', ['id_compra' => $compra->id_compra, 'estado' => 'Pagada']);
        $this->assertDatabaseHas('proveedores', ['id_proveedor' => $proveedor->id_proveedor, 'saldo_pendiente' => 0]);
    }

    public function test_abono_cannot_exceed_balance_or_be_added_to_paid_purchase(): void
    {
        $admin = $this->crearAdmin();
        $proveedor = $this->crearProveedor();
        $proveedor->update(['saldo_pendiente' => 100]);
        $compra = Compra::create([
            'id_proveedor' => $proveedor->id_proveedor,
            'fecha_compra' => now(),
            'subtotal' => 100,
            'impuesto' => 0,
            'total' => 100,
            'estado' => 'Pendiente',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.compras.abonos.store', $compra), ['monto' => 100.01])
            ->assertSessionHasErrors('monto');

        $this->assertDatabaseCount('abonos', 0);
        $this->assertDatabaseHas('compras', ['id_compra' => $compra->id_compra, 'estado' => 'Pendiente']);
        $this->assertDatabaseHas('proveedores', ['id_proveedor' => $proveedor->id_proveedor, 'saldo_pendiente' => 100]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.compras.abonos.store', $compra), ['monto' => 100])
            ->assertSessionHas('success');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.compras.abonos.store', $compra), ['monto' => 1])
            ->assertSessionHasErrors('monto');

        $this->assertDatabaseCount('abonos', 1);
        $this->assertDatabaseHas('compras', ['id_compra' => $compra->id_compra, 'estado' => 'Pagada']);
    }

    public function test_admin_can_download_purchase_pdf(): void
    {
        $admin = $this->crearAdmin();
        $proveedor = $this->crearProveedor('Proveedor PDF');
        $producto = $this->crearProducto('Repuesto PDF');
        $compra = Compra::create([
            'id_proveedor' => $proveedor->id_proveedor,
            'numero_factura' => 'FAC-PDF',
            'fecha_compra' => now(),
            'subtotal' => 50,
            'impuesto' => 6,
            'total' => 56,
            'estado' => 'Pendiente',
        ]);
        DetalleCompra::create([
            'id_compra' => $compra->id_compra,
            'id_producto' => $producto->id_producto,
            'cantidad' => 1,
            'precio_costo_unitario' => 50,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.compras.pdf', $compra));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('compra_'.$compra->id_compra.'_', (string) $response->headers->get('content-disposition'));
    }

    public function test_admin_dashboard_shows_purchase_and_payables_summary(): void
    {
        $admin = $this->crearAdmin();
        $proveedor = $this->crearProveedor('Proveedor del Dashboard');
        $proveedor->update(['saldo_pendiente' => 150]);
        Compra::create([
            'id_proveedor' => $proveedor->id_proveedor,
            'numero_factura' => 'FAC-DASHBOARD',
            'fecha_compra' => now(),
            'fecha_vencimiento' => now()->addDays(15),
            'subtotal' => 150,
            'impuesto' => 0,
            'total' => 150,
            'estado' => 'Pendiente',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Compras del mes')
            ->assertSee('Cuentas por pagar')
            ->assertSee('Compras por pagar')
            ->assertSee('Proveedor del Dashboard')
            ->assertSee('FAC-DASHBOARD')
            ->assertSee('$150.00');
    }

    public function test_admin_can_filter_purchases_by_text_state_and_date(): void
    {
        $admin = $this->crearAdmin();
        $proveedorA = $this->crearProveedor('Repuestos Andinos');
        $proveedorB = $this->crearProveedor('Otro Proveedor');

        Compra::create([
            'id_proveedor' => $proveedorA->id_proveedor,
            'numero_factura' => 'FAC-ENERO',
            'fecha_compra' => '2026-01-15 10:00:00',
            'subtotal' => 100,
            'impuesto' => 0,
            'total' => 100,
            'estado' => 'Pendiente',
        ]);
        Compra::create([
            'id_proveedor' => $proveedorB->id_proveedor,
            'numero_factura' => 'FAC-FEBRERO',
            'fecha_compra' => '2026-02-15 10:00:00',
            'subtotal' => 200,
            'impuesto' => 0,
            'total' => 200,
            'estado' => 'Pagada',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.compras.index', [
                'q' => 'Andinos',
                'estado' => 'Pendiente',
                'desde' => '2026-01-01',
                'hasta' => '2026-01-31',
            ]))
            ->assertOk()
            ->assertSee('FAC-ENERO')
            ->assertDontSee('FAC-FEBRERO');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.compras.index', ['q' => 'FAC-FEBRERO']))
            ->assertOk()
            ->assertSee('FAC-FEBRERO')
            ->assertDontSee('FAC-ENERO');
    }
}
