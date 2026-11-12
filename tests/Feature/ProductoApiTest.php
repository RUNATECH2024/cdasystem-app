<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Subcategoria;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductoApiTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(): Usuario
    {
        return Usuario::create([
            'nombre' => 'Admin',
            'usuario' => 'admin',
            'password_hash' => Hash::make('admin123'),
            'rol' => 'Admin',
            'activo' => true,
        ]);
    }

    private function crearSubcategoria(): Subcategoria
    {
        $categoria = Categoria::create(['nombre' => 'Computadoras', 'activo' => true]);

        return Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'Laptops',
            'activo' => true,
        ]);
    }

    public function test_guest_cannot_create_a_product(): void
    {
        $this->postJson('/api/productos', [
            'nombre' => 'Producto sin token',
            'precio_venta' => 100,
        ])->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_a_product(): void
    {
        $this->actingAs($this->crearUsuario(), 'sanctum');

        $subcategoria = $this->crearSubcategoria();

        $response = $this->postJson('/api/productos', [
            'id_subcategoria' => $subcategoria->id_subcategoria,
            'sku' => 'LAP-001',
            'codigo_barras' => '7501234567000',
            'nombre' => 'Laptop Nueva',
            'marca' => 'Lenovo',
            'descripcion_corta' => 'Laptop de prueba',
            'precio_compra' => 800,
            'precio_venta' => 1200,
            'stock_actual' => 7,
            'stock_minimo' => 2,
            'destacado' => true,
            'es_repuesto' => false,
            'activo' => true,
            'atributos' => [
                ['nombre_atributo' => 'RAM', 'valor_atributo' => '16GB'],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('producto.nombre', 'Laptop Nueva')
            ->assertJsonPath('producto.sku', 'LAP-001')
            ->assertJsonPath('producto.marca', 'Lenovo')
            ->assertJsonPath('producto.stock_actual', 7)
            ->assertJsonCount(1, 'producto.atributos');

        $this->assertDatabaseHas('productos', [
            'sku' => 'LAP-001',
            'nombre' => 'Laptop Nueva',
            'stock_actual' => 7,
        ]);

        $this->assertDatabaseHas('atributos_producto', [
            'nombre_atributo' => 'RAM',
            'valor_atributo' => '16GB',
        ]);
    }

    public function test_it_validates_required_fields(): void
    {
        $this->actingAs($this->crearUsuario(), 'sanctum');

        $this->postJson('/api/productos', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre', 'precio_venta']);
    }

    public function test_it_rejects_duplicate_sku(): void
    {
        $this->actingAs($this->crearUsuario(), 'sanctum');

        $payload = [
            'sku' => 'DUP-001',
            'nombre' => 'Producto 1',
            'precio_venta' => 50,
        ];

        $this->postJson('/api/productos', $payload)->assertCreated();

        $this->postJson('/api/productos', [
            'sku' => 'DUP-001',
            'nombre' => 'Producto 2',
            'precio_venta' => 60,
        ])->assertUnprocessable()->assertJsonValidationErrors(['sku']);
    }
}
