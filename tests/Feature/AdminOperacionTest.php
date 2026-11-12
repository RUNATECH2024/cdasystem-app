<?php

namespace Tests\Feature;

use App\Models\AtributoProducto;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\DetallePedidoWeb;
use App\Models\EquipoCliente;
use App\Models\Garantia;
use App\Models\MovimientoInventario;
use App\Models\OrdenServicio;
use App\Models\PedidoWeb;
use App\Models\Producto;
use App\Models\Subcategoria;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminOperacionTest extends TestCase
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

    public function test_guest_cannot_view_web_orders_or_workshop_orders(): void
    {
        $this->get(route('admin.pedidos.index'))->assertRedirect('/admin/login');
        $this->get(route('admin.ordenes.index'))->assertRedirect('/admin/login');
        $this->get(route('admin.ordenes.create'))->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_and_filter_web_orders(): void
    {
        $admin = $this->crearAdmin();
        $cliente = Cliente::create([
            'nombre' => 'Cliente Pedido',
            'telefono' => '555000100',
            'email' => 'pedido@example.test',
            'activo' => true,
        ]);

        PedidoWeb::create([
            'id_cliente' => $cliente->id_cliente,
            'fecha_pedido' => now(),
            'estado_pedido' => 'Pendiente',
            'subtotal' => 80,
            'costo_envio' => 5,
            'total' => 85,
            'metodo_pago' => 'Transferencia',
            'nombre_contacto' => 'Cliente Pedido',
            'email_contacto' => 'pedido@example.test',
            'telefono_contacto' => '555000100',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.pedidos.index', ['q' => 'Cliente Pedido', 'estado' => 'Pendiente']))
            ->assertOk()
            ->assertSee('Pedidos web')
            ->assertSee('Cliente Pedido')
            ->assertSee('Transferencia')
            ->assertSee('85.00');
    }

    public function test_admin_can_view_and_filter_workshop_orders(): void
    {
        $admin = $this->crearAdmin();
        $cliente = Cliente::create([
            'nombre' => 'Cliente Taller',
            'telefono' => '555000200',
            'email' => 'taller@example.test',
            'activo' => true,
        ]);
        $equipo = EquipoCliente::create([
            'id_cliente' => $cliente->id_cliente,
            'tipo_equipo' => 'Impresora',
            'marca' => 'CDA',
            'modelo' => 'Laser 200',
            'numero_serie' => 'SERIE-TALLER-1',
        ]);

        OrdenServicio::create([
            'id_cliente' => $cliente->id_cliente,
            'id_equipo' => $equipo->id_equipo,
            'fecha_ingreso' => now(),
            'falla_reportada' => 'No enciende',
            'diagnostico' => 'Fuente dañada',
            'estado' => 'En proceso',
            'total_general' => 42.50,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.ordenes.index', ['q' => 'SERIE-TALLER-1', 'estado' => 'En proceso']))
            ->assertOk()
            ->assertSee('Órdenes de taller')
            ->assertSee('Cliente Taller')
            ->assertSee('No enciende')
            ->assertSee('En proceso')
            ->assertSee('42.50');
    }

    public function test_admin_can_create_workshop_order_for_a_clients_equipment(): void
    {
        $admin = $this->crearAdmin();
        $cliente = Cliente::create([
            'nombre' => 'Cliente Nueva Orden',
            'telefono' => '555000700',
            'activo' => true,
        ]);
        $equipo = EquipoCliente::create([
            'id_cliente' => $cliente->id_cliente,
            'tipo_equipo' => 'Impresora',
            'marca' => 'CDA',
            'modelo' => 'Laser 400',
            'numero_serie' => 'SERIE-NUEVA-ORDEN',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.ordenes.create'))
            ->assertOk()
            ->assertSee('Nueva orden de taller')
            ->assertSee('SERIE-NUEVA-ORDEN');

        $this->post(route('admin.ordenes.store'), [
            'id_cliente' => $cliente->id_cliente,
            'id_equipo' => $equipo->id_equipo,
            'falla_reportada' => 'No imprime los documentos',
            'diagnostico' => 'Pendiente de revisión',
            'contador_ingreso' => 1200,
            'total_mano_obra' => 35.50,
        ])->assertRedirect(route('admin.ordenes.show', 1));

        $this->assertDatabaseHas('ordenes_servicio', [
            'id_cliente' => $cliente->id_cliente,
            'id_equipo' => $equipo->id_equipo,
            'falla_reportada' => 'No imprime los documentos',
            'estado' => 'Pendiente',
            'total_mano_obra' => 35.50,
            'total_repuestos' => 0,
            'total_general' => 35.50,
        ]);
    }

    public function test_admin_cannot_create_workshop_order_with_another_clients_equipment(): void
    {
        $admin = $this->crearAdmin();
        $cliente = Cliente::create(['nombre' => 'Cliente A', 'telefono' => '555000801', 'activo' => true]);
        $otroCliente = Cliente::create(['nombre' => 'Cliente B', 'telefono' => '555000802', 'activo' => true]);
        $equipo = EquipoCliente::create([
            'id_cliente' => $otroCliente->id_cliente,
            'tipo_equipo' => 'Laptop',
            'marca' => 'CDA',
        ]);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.ordenes.create'))
            ->post(route('admin.ordenes.store'), [
                'id_cliente' => $cliente->id_cliente,
                'id_equipo' => $equipo->id_equipo,
                'falla_reportada' => 'No enciende',
                'total_mano_obra' => 0,
            ])
            ->assertRedirect(route('admin.ordenes.create'))
            ->assertSessionHasErrors('id_equipo');

        $this->assertDatabaseCount('ordenes_servicio', 0);
    }

    public function test_admin_can_view_complete_web_order_details_and_start_preparation(): void
    {
        $admin = $this->crearAdmin();
        $cliente = Cliente::create([
            'nombre' => 'Cliente Pedido Completo',
            'telefono' => '555000300',
            'dni_ruc' => '1234567890',
            'activo' => true,
        ]);
        $pedido = PedidoWeb::create([
            'id_cliente' => $cliente->id_cliente,
            'fecha_pedido' => now(),
            'estado_pedido' => 'Pendiente',
            'subtotal' => 10,
            'total' => 10,
            'nombre_contacto' => 'Contacto Completo',
        ]);
        $categoria = Categoria::create(['nombre' => 'Computadoras', 'activo' => true]);
        $subcategoria = Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'Laptops',
            'activo' => true,
        ]);
        $producto = Producto::create([
            'id_subcategoria' => $subcategoria->id_subcategoria,
            'nombre' => 'Laptop de prueba',
            'sku' => 'LAP-001',
            'codigo_barras' => '1234567890123',
            'marca' => 'CDA',
            'descripcion_corta' => 'Laptop empresarial',
            'precio_compra' => 5,
            'precio_venta' => 10,
            'stock_actual' => 1,
            'condicion' => 'Nuevo',
            'garantia_meses' => 12,
            'activo' => true,
        ]);
        AtributoProducto::create([
            'id_producto' => $producto->id_producto,
            'nombre_atributo' => 'Memoria',
            'valor_atributo' => '16 GB',
        ]);
        $detalle = DetallePedidoWeb::create([
            'id_pedido' => $pedido->id_pedido,
            'id_producto' => $producto->id_producto,
            'cantidad' => 1,
            'precio_unitario' => 10,
        ]);
        Garantia::create([
            'id_detalle_pedido' => $detalle->id_detalle,
            'numero_serie_equipo' => 'SERIE-LAP-1',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addYear()->toDateString(),
            'estado' => 'Activa',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.pedidos.show', $pedido))
            ->assertOk()
            ->assertSee('Contacto Completo')
            ->assertSee('Laptop de prueba')
            ->assertSee('SERIE-LAP-1')
            ->assertSee('Memoria: 16 GB')
            ->assertSee('12 meses')
            ->assertDontSee('Editar pedido')
            ->assertDontSee('Eliminar');

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.pedidos.atendido', $pedido))
            ->assertRedirect();

        $this->assertDatabaseHas('pedidos_web', [
            'id_pedido' => $pedido->id_pedido,
            'estado_pedido' => 'Preparando',
        ]);
    }

    public function test_admin_can_view_update_and_delete_workshop_order_without_parts(): void
    {
        $admin = $this->crearAdmin();
        $cliente = Cliente::create([
            'nombre' => 'Cliente Orden Editable',
            'telefono' => '555000500',
            'activo' => true,
        ]);
        $equipo = EquipoCliente::create([
            'id_cliente' => $cliente->id_cliente,
            'tipo_equipo' => 'Impresora',
            'marca' => 'CDA',
            'modelo' => 'Laser 300',
        ]);
        $orden = OrdenServicio::create([
            'id_cliente' => $cliente->id_cliente,
            'id_equipo' => $equipo->id_equipo,
            'fecha_ingreso' => now(),
            'falla_reportada' => 'Atasco de papel',
            'estado' => 'Pendiente',
            'total_general' => 20,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.ordenes.show', $orden))
            ->assertOk()
            ->assertSee('Atasco de papel');

        $pdf = $this->get(route('admin.ordenes.pdf', $orden));
        $pdf->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $this->put(route('admin.ordenes.update', $orden), [
            'estado' => 'En Revisión',
            'diagnostico' => 'Rodillo desgastado',
            'contador_salida' => 1200,
        ])->assertRedirect(route('admin.ordenes.show', $orden));

        $this->assertDatabaseHas('ordenes_servicio', [
            'id_orden' => $orden->id_orden,
            'estado' => 'En Revisión',
            'diagnostico' => 'Rodillo desgastado',
            'contador_salida' => 1200,
        ]);

        $this->put(route('admin.ordenes.update', $orden), [
            'estado' => 'En proceso',
        ])->assertSessionHasErrors('estado');

        $this->assertSame('En Revisión', $orden->fresh()->estado);

        $this->delete(route('admin.ordenes.destroy', $orden))
            ->assertRedirect(route('admin.ordenes.index'));
        $this->assertDatabaseMissing('ordenes_servicio', ['id_orden' => $orden->id_orden]);
    }

    public function test_workshop_order_with_inventory_movement_cannot_be_deleted(): void
    {
        $admin = $this->crearAdmin();
        $cliente = Cliente::create([
            'nombre' => 'Cliente con movimiento',
            'telefono' => '555000600',
            'activo' => true,
        ]);
        $equipo = EquipoCliente::create([
            'id_cliente' => $cliente->id_cliente,
            'tipo_equipo' => 'Impresora',
        ]);
        $orden = OrdenServicio::create([
            'id_cliente' => $cliente->id_cliente,
            'id_equipo' => $equipo->id_equipo,
            'fecha_ingreso' => now(),
            'falla_reportada' => 'Revisión general',
            'estado' => 'Pendiente',
            'total_general' => 0,
        ]);
        $producto = Producto::create([
            'nombre' => 'Pieza registrada en movimiento',
            'sku' => 'MOV-'.uniqid(),
            'precio_venta' => 10,
            'stock_actual' => 0,
            'activo' => true,
        ]);
        MovimientoInventario::create([
            'id_producto' => $producto->id_producto,
            'tipo_movimiento' => 'SALIDA',
            'origen' => 'TALLER',
            'cantidad' => 1,
            'stock_anterior' => 1,
            'stock_nuevo' => 0,
            'motivo' => 'Orden de servicio #'.$orden->id_orden,
            'id_referencia' => $orden->id_orden,
            'fecha_movimiento' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.ordenes.destroy', $orden))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('ordenes_servicio', ['id_orden' => $orden->id_orden]);
    }
}
