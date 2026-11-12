<?php

namespace Tests\Feature;

use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProveedorTest extends TestCase
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

    public function test_guest_cannot_access_supplier_management(): void
    {
        $proveedor = Proveedor::create([
            'nombre' => 'Proveedor Privado',
            'activo' => true,
        ]);

        $this->get(route('admin.proveedores.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.proveedores.create'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.proveedores.show', $proveedor))->assertRedirect(route('admin.login'));
        $this->get(route('admin.proveedores.edit', $proveedor))->assertRedirect(route('admin.login'));
        $this->post(route('admin.proveedores.store'), [])->assertRedirect(route('admin.login'));
        $this->delete(route('admin.proveedores.destroy', $proveedor))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_open_supplier_forms(): void
    {
        $admin = $this->crearAdmin();
        $proveedor = Proveedor::create([
            'nombre' => 'Proveedor Formulario',
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.proveedores.create'))
            ->assertOk()
            ->assertSee('Nuevo proveedor');
        $this->actingAs($admin, 'admin')
            ->get(route('admin.proveedores.edit', $proveedor))
            ->assertOk()
            ->assertSee('Proveedor Formulario');
    }

    public function test_admin_can_create_a_supplier(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.proveedores.store'), [
                'ruc' => '20123456789',
                'nombre' => 'Distribuidora Central',
                'telefono' => '555-8000',
                'email' => 'VENTAS@Example.Test',
                'direccion' => 'Av. Industrial 123',
                'activo' => '1',
            ]);

        $proveedor = Proveedor::where('ruc', '20123456789')->firstOrFail();

        $response
            ->assertRedirect(route('admin.proveedores.show', $proveedor))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('proveedores', [
            'id_proveedor' => $proveedor->id_proveedor,
            'nombre' => 'Distribuidora Central',
            'telefono' => '555-8000',
            'email' => 'ventas@example.test',
            'direccion' => 'Av. Industrial 123',
            'activo' => true,
        ]);
    }

    public function test_suppliers_can_be_created_without_optional_fields(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.proveedores.store'), ['nombre' => 'Proveedor Sin Datos'])
            ->assertSessionHasNoErrors();
        $this->actingAs($admin, 'admin')
            ->post(route('admin.proveedores.store'), ['nombre' => 'Otro Proveedor Sin Datos'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('proveedores', [
            'nombre' => 'Proveedor Sin Datos',
            'ruc' => null,
            'email' => null,
            'direccion' => null,
        ]);
        $this->assertDatabaseHas('proveedores', ['nombre' => 'Otro Proveedor Sin Datos']);
    }

    public function test_supplier_ruc_and_email_must_be_unique(): void
    {
        $admin = $this->crearAdmin();

        Proveedor::create([
            'ruc' => '20123456789',
            'nombre' => 'Proveedor Existente',
            'email' => 'existente@example.test',
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.proveedores.store'), [
                'ruc' => '20123456789',
                'nombre' => 'Proveedor Duplicado',
                'email' => 'EXISTENTE@Example.Test',
            ])
            ->assertSessionHasErrors(['ruc', 'email']);

        $this->assertDatabaseMissing('proveedores', ['nombre' => 'Proveedor Duplicado']);
    }

    public function test_admin_can_search_suppliers_by_name_ruc_phone_or_email(): void
    {
        $admin = $this->crearAdmin();

        Proveedor::create([
            'ruc' => '20999999999',
            'nombre' => 'Insumos del Norte',
            'telefono' => '555-7000',
            'email' => 'norte@example.test',
            'activo' => true,
        ]);
        Proveedor::create([
            'nombre' => 'Proveedor Descartado',
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.proveedores.index', ['q' => '20999999999']))
            ->assertOk()
            ->assertSee('Insumos del Norte')
            ->assertDontSee('Proveedor Descartado');
    }

    public function test_admin_can_view_supplier_details_with_purchases(): void
    {
        $admin = $this->crearAdmin();

        $proveedor = Proveedor::create([
            'ruc' => '20888888888',
            'nombre' => 'Proveedor Detalle',
            'saldo_pendiente' => 250,
            'activo' => true,
        ]);
        $compra = Compra::create([
            'id_proveedor' => $proveedor->id_proveedor,
            'numero_factura' => 'FAC-001',
            'fecha_compra' => now(),
            'subtotal' => 220,
            'impuesto' => 30,
            'total' => 250,
            'estado' => 'Pendiente',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.proveedores.show', $proveedor))
            ->assertOk()
            ->assertSee('Proveedor Detalle')
            ->assertSee('FAC-001')
            ->assertSee('#'.$compra->id_compra)
            ->assertSee('$250.00');
    }

    public function test_admin_can_update_a_supplier(): void
    {
        $admin = $this->crearAdmin();

        $proveedor = Proveedor::create([
            'ruc' => '20777777777',
            'nombre' => 'Nombre Anterior',
            'telefono' => '555-6000',
            'email' => 'anterior@example.test',
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.proveedores.update', $proveedor), [
                'ruc' => '20777777777',
                'nombre' => 'Nombre Actualizado',
                'telefono' => '555-6100',
                'email' => 'ACTUALIZADO@Example.Test',
                'direccion' => 'Nueva dirección 456',
                'activo' => '1',
            ])
            ->assertRedirect(route('admin.proveedores.show', $proveedor))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('proveedores', [
            'id_proveedor' => $proveedor->id_proveedor,
            'nombre' => 'Nombre Actualizado',
            'telefono' => '555-6100',
            'email' => 'actualizado@example.test',
            'direccion' => 'Nueva dirección 456',
            'activo' => true,
        ]);
    }

    public function test_admin_can_deactivate_and_reactivate_a_supplier(): void
    {
        $admin = $this->crearAdmin();

        $proveedor = Proveedor::create([
            'nombre' => 'Proveedor Estado',
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.proveedores.estado', $proveedor))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('proveedores', [
            'id_proveedor' => $proveedor->id_proveedor,
            'activo' => false,
        ]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.proveedores.estado', $proveedor))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('proveedores', [
            'id_proveedor' => $proveedor->id_proveedor,
            'activo' => true,
        ]);
    }

    public function test_admin_can_delete_a_supplier_without_history(): void
    {
        $admin = $this->crearAdmin();

        $proveedor = Proveedor::create([
            'nombre' => 'Proveedor Eliminable',
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.proveedores.destroy', $proveedor))
            ->assertRedirect(route('admin.proveedores.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('proveedores', ['id_proveedor' => $proveedor->id_proveedor]);
    }

    public function test_admin_cannot_delete_a_supplier_with_purchases_or_pending_balance(): void
    {
        $admin = $this->crearAdmin();

        $conCompras = Proveedor::create([
            'nombre' => 'Proveedor Con Compras',
            'activo' => true,
        ]);
        Compra::create([
            'id_proveedor' => $conCompras->id_proveedor,
            'numero_factura' => 'FAC-002',
            'fecha_compra' => now(),
            'subtotal' => 100,
            'impuesto' => 12,
            'total' => 112,
            'estado' => 'Pagada',
        ]);
        $conSaldo = Proveedor::create([
            'nombre' => 'Proveedor Con Saldo',
            'saldo_pendiente' => 50,
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.proveedores.destroy', $conCompras))
            ->assertSessionHas('error');
        $this->actingAs($admin, 'admin')
            ->delete(route('admin.proveedores.destroy', $conSaldo))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('proveedores', ['id_proveedor' => $conCompras->id_proveedor]);
        $this->assertDatabaseHas('proveedores', ['id_proveedor' => $conSaldo->id_proveedor]);
    }
}
