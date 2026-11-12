<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\EquipoCliente;
use App\Models\OrdenServicio;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminClienteTest extends TestCase
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

    public function test_guest_cannot_access_client_creation(): void
    {
        $this->get(route('admin.clientes.create'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.clientes.store'), [])->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_create_a_client_and_its_first_equipment(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.clientes.store'), [
                'nombre' => 'Cliente Nuevo',
                'dni_ruc' => '12345678',
                'telefono' => '555-1234',
                'email' => 'Nuevo@Example.Test',
                'direccion' => 'Av. Principal 123',
                'tipo_equipo' => 'Impresora',
                'marca' => 'HP',
                'modelo' => 'LaserJet',
                'numero_serie' => 'SERIE-NUEVA-1',
                'contador_paginas' => 100,
                'observaciones' => 'Equipo de prueba',
            ]);

        $cliente = Cliente::where('email', 'nuevo@example.test')->firstOrFail();
        $equipo = EquipoCliente::where('numero_serie', 'SERIE-NUEVA-1')->firstOrFail();

        $response
            ->assertRedirect(route('admin.ordenes.create', [
                'cliente' => $cliente->id_cliente,
                'equipo' => $equipo->id_equipo,
            ]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('clientes', [
            'id_cliente' => $cliente->id_cliente,
            'nombre' => 'Cliente Nuevo',
            'telefono' => '555-1234',
            'activo' => true,
        ]);

        $this->assertDatabaseHas('equipos_cliente', [
            'id_equipo' => $equipo->id_equipo,
            'id_cliente' => $cliente->id_cliente,
            'tipo_equipo' => 'Impresora',
            'numero_serie' => 'SERIE-NUEVA-1',
        ]);
    }

    public function test_admin_can_create_a_client_without_equipment(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.clientes.store'), [
                'nombre' => 'Cliente Sin Equipo',
                'telefono' => '555-5678',
            ]);

        $cliente = Cliente::where('nombre', 'Cliente Sin Equipo')->firstOrFail();

        $response->assertRedirect(route('admin.ordenes.create', [
            'cliente' => $cliente->id_cliente,
        ]));

        $this->assertDatabaseCount('equipos_cliente', 0);
    }

    public function test_new_client_and_equipment_are_preselected_on_the_order_form(): void
    {
        $admin = $this->crearAdmin();

        $cliente = Cliente::create([
            'nombre' => 'Cliente Preseleccionado',
            'telefono' => '555-2222',
            'activo' => true,
        ]);
        $equipo = EquipoCliente::create([
            'id_cliente' => $cliente->id_cliente,
            'tipo_equipo' => 'Laptop',
            'numero_serie' => 'SERIE-PRESELECCION',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.ordenes.create', [
            'cliente' => $cliente->id_cliente,
            'equipo' => $equipo->id_equipo,
        ]));

        $response->assertOk();

        $html = $response->getContent();

        $this->assertMatchesRegularExpression('/value="'.$cliente->id_cliente.'"[^>]*selected/', $html);
        $this->assertMatchesRegularExpression('/value="'.$equipo->id_equipo.'"[^>]*selected/', $html);
    }

    public function test_client_email_must_be_unique_ignoring_case(): void
    {
        $admin = $this->crearAdmin();

        Cliente::create([
            'nombre' => 'Cliente Existente',
            'telefono' => '555-0000',
            'email' => 'existente@example.test',
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.clientes.store'), [
                'nombre' => 'Cliente Duplicado',
                'telefono' => '555-1111',
                'email' => 'Existente@Example.Test',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('clientes', 1);
    }

    public function test_guest_cannot_access_client_management(): void
    {
        $cliente = Cliente::create([
            'nombre' => 'Cliente Privado',
            'telefono' => '555-9000',
            'activo' => true,
        ]);

        $this->get(route('admin.clientes.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.clientes.show', $cliente))->assertRedirect(route('admin.login'));
        $this->get(route('admin.clientes.edit', $cliente))->assertRedirect(route('admin.login'));
        $this->delete(route('admin.clientes.destroy', $cliente))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_search_clients_by_name_or_equipment_serial(): void
    {
        $admin = $this->crearAdmin();

        $cliente = Cliente::create([
            'nombre' => 'María Buscada',
            'telefono' => '555-1010',
            'activo' => true,
        ]);
        EquipoCliente::create([
            'id_cliente' => $cliente->id_cliente,
            'tipo_equipo' => 'Impresora',
            'numero_serie' => 'SERIE-BUSQUEDA-99',
        ]);
        Cliente::create([
            'nombre' => 'Cliente Descartado',
            'telefono' => '555-2020',
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.clientes.index', ['q' => 'SERIE-BUSQUEDA-99']))
            ->assertOk()
            ->assertSee('María Buscada')
            ->assertDontSee('Cliente Descartado');
    }

    public function test_admin_can_view_client_details_with_equipment_and_orders(): void
    {
        $admin = $this->crearAdmin();

        $cliente = Cliente::create([
            'nombre' => 'Cliente Detalle',
            'telefono' => '555-3030',
            'email' => 'detalle@example.test',
            'activo' => true,
        ]);
        $equipo = EquipoCliente::create([
            'id_cliente' => $cliente->id_cliente,
            'tipo_equipo' => 'Laptop',
            'marca' => 'Lenovo',
            'numero_serie' => 'SERIE-DETALLE-1',
        ]);
        $orden = OrdenServicio::create([
            'id_cliente' => $cliente->id_cliente,
            'id_equipo' => $equipo->id_equipo,
            'fecha_ingreso' => now(),
            'falla_reportada' => 'No enciende',
            'estado' => 'Pendiente',
            'total_mano_obra' => 0,
            'total_repuestos' => 0,
            'total_general' => 0,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.clientes.show', $cliente))
            ->assertOk()
            ->assertSee('Cliente Detalle')
            ->assertSee('SERIE-DETALLE-1')
            ->assertSee('#'.$orden->id_orden);
    }

    public function test_admin_can_update_a_client(): void
    {
        $admin = $this->crearAdmin();

        $cliente = Cliente::create([
            'nombre' => 'Nombre Anterior',
            'telefono' => '555-4040',
            'email' => 'anterior@example.test',
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.clientes.update', $cliente), [
                'nombre' => 'Nombre Actualizado',
                'dni_ruc' => '87654321',
                'telefono' => '555-4141',
                'email' => 'ACTUALIZADO@Example.Test',
                'direccion' => 'Nueva dirección 456',
                'activo' => '1',
            ])
            ->assertRedirect(route('admin.clientes.show', $cliente))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('clientes', [
            'id_cliente' => $cliente->id_cliente,
            'nombre' => 'Nombre Actualizado',
            'dni_ruc' => '87654321',
            'telefono' => '555-4141',
            'email' => 'actualizado@example.test',
            'direccion' => 'Nueva dirección 456',
            'activo' => true,
        ]);
    }

    public function test_admin_can_deactivate_and_reactivate_a_client(): void
    {
        $admin = $this->crearAdmin();

        $cliente = Cliente::create([
            'nombre' => 'Cliente Estado',
            'telefono' => '555-5050',
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.clientes.estado', $cliente))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('clientes', [
            'id_cliente' => $cliente->id_cliente,
            'activo' => false,
        ]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.clientes.estado', $cliente))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('clientes', [
            'id_cliente' => $cliente->id_cliente,
            'activo' => true,
        ]);
    }

    public function test_admin_can_delete_a_client_without_history(): void
    {
        $admin = $this->crearAdmin();

        $cliente = Cliente::create([
            'nombre' => 'Cliente Eliminable',
            'telefono' => '555-6060',
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.clientes.destroy', $cliente))
            ->assertRedirect(route('admin.clientes.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('clientes', ['id_cliente' => $cliente->id_cliente]);
    }

    public function test_admin_cannot_delete_a_client_with_order_history(): void
    {
        $admin = $this->crearAdmin();

        $cliente = Cliente::create([
            'nombre' => 'Cliente Con Historial',
            'telefono' => '555-7070',
            'activo' => true,
        ]);
        $equipo = EquipoCliente::create([
            'id_cliente' => $cliente->id_cliente,
            'tipo_equipo' => 'Impresora',
        ]);
        OrdenServicio::create([
            'id_cliente' => $cliente->id_cliente,
            'id_equipo' => $equipo->id_equipo,
            'fecha_ingreso' => now(),
            'falla_reportada' => 'Atasco de papel',
            'estado' => 'Pendiente',
            'total_mano_obra' => 0,
            'total_repuestos' => 0,
            'total_general' => 0,
        ]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.clientes.destroy', $cliente))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('clientes', ['id_cliente' => $cliente->id_cliente]);
    }
}
