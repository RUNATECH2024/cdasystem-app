<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\EquipoCliente;
use App\Models\OrdenServicio;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TallerApiTest extends TestCase
{
    use RefreshDatabase;

    private function crearTecnico(): Usuario
    {
        return Usuario::create([
            'nombre' => 'Técnico',
            'usuario' => 'tecnico',
            'password_hash' => Hash::make('secret123'),
            'rol' => 'Tecnico',
            'activo' => true,
        ]);
    }

    private function crearCliente(string $nombre = 'Cliente Test'): Cliente
    {
        return Cliente::create([
            'nombre' => $nombre,
            'telefono' => '999999999',
            'activo' => true,
        ]);
    }

    private function crearEquipo(Cliente $cliente): EquipoCliente
    {
        return EquipoCliente::create([
            'id_cliente' => $cliente->id_cliente,
            'tipo_equipo' => 'Impresora',
            'numero_serie' => 'SERIE-'.uniqid(),
        ]);
    }

    public function test_it_creates_a_service_order(): void
    {
        $tecnico = $this->crearTecnico();
        $cliente = $this->crearCliente();
        $equipo = $this->crearEquipo($cliente);

        $this->actingAs($tecnico, 'sanctum');

        $response = $this->postJson('/api/taller/ordenes', [
            'id_cliente' => $cliente->id_cliente,
            'id_equipo' => $equipo->id_equipo,
            'falla_reportada' => 'No enciende',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('ordenes_servicio', [
            'id_cliente' => $cliente->id_cliente,
            'id_equipo' => $equipo->id_equipo,
            'id_tecnico' => $tecnico->id_usuario,
            'estado' => 'Pendiente',
        ]);
    }

    public function test_it_rejects_an_equipment_from_another_client(): void
    {
        $tecnico = $this->crearTecnico();
        $cliente = $this->crearCliente('Cliente A');
        $otroCliente = $this->crearCliente('Cliente B');
        $equipoAjeno = $this->crearEquipo($otroCliente);

        $this->actingAs($tecnico, 'sanctum');

        $this->postJson('/api/taller/ordenes', [
            'id_cliente' => $cliente->id_cliente,
            'id_equipo' => $equipoAjeno->id_equipo,
            'falla_reportada' => 'No enciende',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('id_equipo');
    }

    public function test_it_adds_a_spare_part_and_updates_totals(): void
    {
        $tecnico = $this->crearTecnico();
        $cliente = $this->crearCliente();
        $equipo = $this->crearEquipo($cliente);

        $orden = OrdenServicio::create([
            'id_cliente' => $cliente->id_cliente,
            'id_equipo' => $equipo->id_equipo,
            'id_tecnico' => $tecnico->id_usuario,
            'falla_reportada' => 'Atasco de papel',
            'estado' => 'Pendiente',
            'total_mano_obra' => 50,
            'total_repuestos' => 0,
            'total_general' => 50,
        ]);

        $producto = Producto::create([
            'nombre' => 'Repuesto Test',
            'sku' => 'REP-'.uniqid(),
            'precio_venta' => 30,
            'stock_actual' => 10,
            'activo' => true,
            'es_repuesto' => true,
        ]);

        $this->actingAs($tecnico, 'sanctum');

        $this->postJson("/api/taller/ordenes/{$orden->id_orden}/repuestos", [
            'id_producto' => $producto->id_producto,
            'cantidad' => 2,
        ])->assertCreated();

        $this->assertDatabaseHas('detalle_repuestos_os', [
            'id_orden' => $orden->id_orden,
            'id_producto' => $producto->id_producto,
            'cantidad' => 2,
            'precio_unitario' => 30,
        ]);

        $orden->refresh();
        $this->assertEquals(60, $orden->total_repuestos);
        $this->assertEquals(110, $orden->total_general);
        $this->assertSame(8, $producto->fresh()->stock_actual);

        $this->assertDatabaseHas('movimientos_inventario', [
            'id_producto' => $producto->id_producto,
            'tipo_movimiento' => 'SALIDA',
            'origen' => 'TALLER',
            'id_referencia' => $orden->id_orden,
        ]);
    }

    public function test_it_cannot_add_a_spare_part_to_a_completed_order(): void
    {
        $tecnico = $this->crearTecnico();
        $cliente = $this->crearCliente();
        $equipo = $this->crearEquipo($cliente);
        $orden = OrdenServicio::create([
            'id_cliente' => $cliente->id_cliente,
            'id_equipo' => $equipo->id_equipo,
            'id_tecnico' => $tecnico->id_usuario,
            'falla_reportada' => 'Equipo reparado',
            'estado' => 'Entregado',
            'total_mano_obra' => 0,
            'total_repuestos' => 0,
            'total_general' => 0,
        ]);
        $producto = Producto::create([
            'nombre' => 'Repuesto bloqueado',
            'sku' => 'REP-BLOCK-'.uniqid(),
            'precio_venta' => 30,
            'stock_actual' => 4,
            'activo' => true,
            'es_repuesto' => true,
        ]);

        $this->actingAs($tecnico, 'sanctum')
            ->postJson("/api/taller/ordenes/{$orden->id_orden}/repuestos", [
                'id_producto' => $producto->id_producto,
                'cantidad' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('id_orden');

        $this->assertSame(4, $producto->fresh()->stock_actual);
        $this->assertDatabaseCount('detalle_repuestos_os', 0);
    }
}
