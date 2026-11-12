<?php

namespace Tests\Feature;

use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WebAuthTest extends TestCase
{
    use RefreshDatabase;

    private function crearCliente(array $overrides = []): Cliente
    {
        return Cliente::create(array_merge([
            'nombre' => 'Cliente Test',
            'email' => 'cliente@test.com',
            'telefono' => '555-1234',
            'password' => Hash::make('secreto123'),
            'activo' => true,
        ], $overrides));
    }

    public function test_home_shows_login_link_for_guests(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Iniciar sesión')
            ->assertSee(route('login'));
    }

    public function test_client_can_login_with_valid_credentials(): void
    {
        $cliente = $this->crearCliente();

        $this->post('/login', [
            'email' => 'cliente@test.com',
            'password' => 'secreto123',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($cliente, 'cliente');
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $this->crearCliente();

        $this->post('/login', [
            'email' => 'cliente@test.com',
            'password' => 'incorrecta',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('cliente');
    }

    public function test_inactive_client_cannot_login(): void
    {
        $this->crearCliente(['activo' => false]);

        $this->post('/login', [
            'email' => 'cliente@test.com',
            'password' => 'secreto123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('cliente');
    }

    public function test_client_can_register(): void
    {
        $this->post('/register', [
            'nombre' => 'Nuevo Cliente',
            'email' => 'nuevo@test.com',
            'telefono' => '555-9999',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
        ])->assertRedirect('/');

        $this->assertAuthenticated('cliente');
        $this->assertDatabaseHas('clientes', ['email' => 'nuevo@test.com']);
    }

    public function test_client_can_logout(): void
    {
        $cliente = $this->crearCliente();

        $this->actingAs($cliente, 'cliente')->post('/logout')->assertRedirect('/');

        $this->assertGuest('cliente');
    }
}
