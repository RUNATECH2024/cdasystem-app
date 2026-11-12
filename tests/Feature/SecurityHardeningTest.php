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

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(string $usuario, string $rol, bool $activo = true): Usuario
    {
        return Usuario::create([
            'nombre' => ucfirst($usuario),
            'usuario' => $usuario,
            'password_hash' => Hash::make('contrasena-segura-123'),
            'rol' => $rol,
            'activo' => $activo,
        ]);
    }

    private function crearCliente(bool $activo = true): Cliente
    {
        return Cliente::create([
            'nombre' => 'Cliente Seguridad',
            'email' => 'seguridad@example.test',
            'telefono' => '555-0000',
            'password' => Hash::make('secreto123'),
            'activo' => $activo,
        ]);
    }

    public function test_security_headers_are_sent_on_web_responses(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-XSS-Protection', '0')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');

        $csp = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
    }

    public function test_client_login_regenerates_the_session_id(): void
    {
        $this->crearCliente();

        $this->withSession(['marcador' => 'antes']);
        $idAnterior = session()->getId();

        $this->post('/login', [
            'email' => 'seguridad@example.test',
            'password' => 'secreto123',
        ])->assertRedirect('/');

        $this->assertNotSame($idAnterior, session()->getId());
        $this->assertAuthenticated('cliente');
    }

    public function test_client_login_normalizes_email_and_rejects_weak_passwords(): void
    {
        $this->post('/register', [
            'nombre' => 'Cliente Correo',
            'email' => 'Correo@Example.Test',
            'telefono' => '555-1111',
            'password' => 'corta',
            'password_confirmation' => 'corta',
        ])->assertSessionHasErrors('password');

        $this->post('/register', [
            'nombre' => 'Cliente Correo',
            'email' => 'Correo@Example.Test',
            'telefono' => '555-1111',
            'password' => 'clave-segura-123',
            'password_confirmation' => 'clave-segura-123',
        ])->assertRedirect('/');

        $this->assertDatabaseHas('clientes', [
            'email' => 'correo@example.test',
        ]);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest('cliente');
    }

    public function test_inactive_client_session_is_closed_on_next_request(): void
    {
        $cliente = $this->crearCliente();

        $this->actingAs($cliente, 'cliente');
        $cliente->update(['activo' => false]);

        $this->get('/')->assertRedirect(route('login'));
        $this->assertGuest('cliente');
    }

    public function test_inactive_admin_session_is_closed_on_next_request(): void
    {
        $admin = $this->crearUsuario('admin', 'Admin');

        $this->actingAs($admin, 'admin');
        $admin->update(['activo' => false]);

        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_inactive_api_user_is_rejected(): void
    {
        $usuario = $this->crearUsuario('inactivo', 'Vendedor', false);

        $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/logout')
            ->assertForbidden()
            ->assertJsonPath('error', 'La cuenta esta desactivada.');
    }

    public function test_api_routes_enforce_staff_roles(): void
    {
        $vendedor = $this->crearUsuario('vendedor', 'Vendedor');
        $tecnico = $this->crearUsuario('tecnico', 'Tecnico');
        $admin = $this->crearUsuario('admin', 'Admin');

        $this->actingAs($vendedor, 'sanctum')
            ->postJson('/api/productos', [])
            ->assertForbidden();

        $this->actingAs($tecnico, 'sanctum')
            ->postJson('/api/ventas', [])
            ->assertForbidden();

        $this->actingAs($vendedor, 'sanctum')
            ->postJson('/api/taller/ordenes', [])
            ->assertForbidden();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/productos', [])
            ->assertUnprocessable();
    }

    public function test_api_logout_revokes_the_current_token(): void
    {
        $usuario = $this->crearUsuario('vendedor', 'Vendedor');
        $token = $usuario->createToken('prueba')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $usuario->id_usuario,
        ]);
    }

    public function test_api_technician_must_be_an_active_technician(): void
    {
        $admin = $this->crearUsuario('admin', 'Admin');
        $vendedor = $this->crearUsuario('vendedor', 'Vendedor');

        $cliente = Cliente::create([
            'nombre' => 'Cliente Taller',
            'telefono' => '555-2222',
            'activo' => true,
        ]);
        $equipo = EquipoCliente::create([
            'id_cliente' => $cliente->id_cliente,
            'tipo_equipo' => 'Laptop',
            'numero_serie' => 'SERIE-API-1',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/taller/ordenes', [
                'id_cliente' => $cliente->id_cliente,
                'id_equipo' => $equipo->id_equipo,
                'id_tecnico' => $vendedor->id_usuario,
                'falla_reportada' => 'No enciende',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('id_tecnico');
    }

    public function test_admin_order_update_rejects_non_technician_assignment(): void
    {
        $admin = $this->crearUsuario('admin', 'Admin');
        $vendedor = $this->crearUsuario('vendedor', 'Vendedor');

        $cliente = Cliente::create([
            'nombre' => 'Cliente Orden',
            'telefono' => '555-3333',
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
            'falla_reportada' => 'Revisar',
            'estado' => 'Pendiente',
            'total_general' => 0,
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.ordenes.update', $orden), [
                'estado' => 'Pendiente',
                'id_tecnico' => $vendedor->id_usuario,
            ])
            ->assertSessionHasErrors('id_tecnico');
    }

    public function test_csv_export_sanitizes_spreadsheet_formulas(): void
    {
        $admin = $this->crearUsuario('admin', 'Admin');

        Producto::create([
            'nombre' => '=1+1',
            'sku' => 'FORMULA-1',
            'precio_compra' => 1,
            'precio_venta' => 2,
            'stock_actual' => 1,
            'stock_minimo' => 0,
            'activo' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.reportes.exportar', ['tipo' => 'inventario']));

        $response->assertOk();

        $this->assertStringContainsString("'=1+1", $response->streamedContent());
    }

    public function test_cors_allows_configured_origins(): void
    {
        $this->withHeader('Origin', 'http://localhost')
            ->getJson('/api/catalogo/menu')
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost');
    }

    public function test_cors_does_not_reflect_unknown_origins(): void
    {
        config(['cors.allowed_origins' => ['http://localhost', 'http://otro-origen.test']]);

        $this->withHeader('Origin', 'https://origen-no-permitido.test')
            ->getJson('/api/catalogo/menu')
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }
}
