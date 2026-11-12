<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VentaApiTest extends TestCase
{
    use RefreshDatabase;

    private function crearVendedor(): Usuario
    {
        return Usuario::create([
            'nombre' => 'Vendedor',
            'usuario' => 'vendedor',
            'password_hash' => Hash::make('secret123'),
            'rol' => 'Vendedor',
            'activo' => true,
        ]);
    }

    private function crearProducto(int $stock = 10): Producto
    {
        return Producto::create([
            'nombre' => 'Producto Test',
            'sku' => 'SKU-'.uniqid(),
            'precio_venta' => 100,
            'stock_actual' => $stock,
            'activo' => true,
        ]);
    }

    public function test_it_registers_a_sale_and_decrements_stock(): void
    {
        $vendedor = $this->crearVendedor();
        $producto = $this->crearProducto(10);

        $this->actingAs($vendedor, 'sanctum');

        $response = $this->postJson('/api/ventas', [
            'items' => [
                ['id_producto' => $producto->id_producto, 'cantidad' => 2],
            ],
            'impuesto' => 0,
        ]);

        $response->assertCreated()
            ->assertJsonPath('venta.id_vendedor', $vendedor->id_usuario);

        $this->assertDatabaseHas('ventas', [
            'id_vendedor' => $vendedor->id_usuario,
            'subtotal' => 200,
            'total' => 200,
        ]);

        $this->assertDatabaseHas('detalle_ventas', [
            'id_producto' => $producto->id_producto,
            'cantidad' => 2,
            'precio_unitario' => 100,
        ]);

        $this->assertDatabaseHas('movimientos_inventario', [
            'id_producto' => $producto->id_producto,
            'tipo_movimiento' => 'SALIDA',
            'origen' => 'VENTA',
            'stock_anterior' => 10,
            'stock_nuevo' => 8,
        ]);

        $this->assertSame(8, $producto->fresh()->stock_actual);
    }

    public function test_it_rejects_a_sale_when_stock_is_insufficient(): void
    {
        $vendedor = $this->crearVendedor();
        $producto = $this->crearProducto(1);

        $this->actingAs($vendedor, 'sanctum');

        $this->postJson('/api/ventas', [
            'items' => [
                ['id_producto' => $producto->id_producto, 'cantidad' => 5],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors('items');

        $this->assertSame(1, $producto->fresh()->stock_actual);
        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_it_rejects_status_and_payment_values_outside_database_constraints(): void
    {
        $vendedor = $this->crearVendedor();
        $producto = $this->crearProducto();

        $this->actingAs($vendedor, 'sanctum')
            ->postJson('/api/ventas', [
                'items' => [['id_producto' => $producto->id_producto, 'cantidad' => 1]],
                'metodo_pago' => 'Bitcoin',
                'estado' => 'Anulada',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['metodo_pago', 'estado']);

        $this->assertDatabaseCount('ventas', 0);
        $this->assertSame(10, $producto->fresh()->stock_actual);
    }
}
