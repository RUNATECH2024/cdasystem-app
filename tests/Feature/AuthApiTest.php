<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(array $atributos = []): Usuario
    {
        return Usuario::create(array_merge([
            'nombre' => 'Administrador',
            'usuario' => 'admin',
            'password_hash' => Hash::make('secret123'),
            'rol' => 'Admin',
            'activo' => true,
        ], $atributos));
    }

    public function test_login_returns_token_with_valid_credentials(): void
    {
        $this->crearUsuario();

        $response = $this->postJson('/api/login', [
            'usuario' => 'admin',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['mensaje', 'token', 'usuario'])
            ->assertJsonMissingPath('usuario.password_hash');
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $this->crearUsuario();

        $this->postJson('/api/login', [
            'usuario' => 'admin',
            'password' => 'incorrecta',
        ])->assertStatus(401);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $this->crearUsuario(['activo' => false]);

        $this->postJson('/api/login', [
            'usuario' => 'admin',
            'password' => 'secret123',
        ])->assertStatus(401);
    }

    public function test_protected_routes_require_authentication(): void
    {
        $this->postJson('/api/ventas', [])->assertStatus(401);
        $this->postJson('/api/taller/ordenes', [])->assertStatus(401);
    }
}
