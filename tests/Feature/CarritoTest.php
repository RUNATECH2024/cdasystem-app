<?php

namespace Tests\Feature;

use App\Mail\PedidoWebComprobante;
use App\Models\Cliente;
use App\Models\PedidoWeb;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CarritoTest extends TestCase
{
    use RefreshDatabase;

    private function crearCliente(): Cliente
    {
        return Cliente::create([
            'nombre' => 'Cliente Carrito',
            'email' => 'carrito@test.com',
            'telefono' => '555-0000',
            'password' => Hash::make('secreto123'),
            'activo' => true,
        ]);
    }

    private function crearProducto(array $overrides = []): Producto
    {
        return Producto::create(array_merge([
            'nombre' => 'LENOVO IdeaPad Intel N150 8GB 128GB',
            'sku' => 'COMLEN83L7002KU',
            'marca' => 'Lenovo',
            'precio_compra' => 300,
            'precio_venta' => 346,
            'stock_actual' => 5,
            'stock_minimo' => 1,
            'activo' => true,
        ], $overrides));
    }

    private function sesionCarrito(Producto $producto, int $cantidad = 1): array
    {
        return [
            $producto->id_producto => [
                'nombre' => $producto->nombre,
                'sku' => $producto->sku,
                'cantidad' => $cantidad,
                'precio' => $producto->precio_venta,
                'imagen' => $producto->imagen_principal,
            ],
        ];
    }

    public function test_cart_page_shows_product_summary_with_iva_and_installments(): void
    {
        $producto = $this->crearProducto();

        $this->withSession(['carrito' => $this->sesionCarrito($producto)])
            ->get(route('carrito.index'))
            ->assertOk()
            ->assertSee('Mi Carrito')
            ->assertSee('COMLEN83L7002KU')
            ->assertSee('LENOVO IdeaPad Intel N150 8GB 128GB')
            ->assertSee('Resumen de Compra')
            ->assertSee('I.V.A (15%)')
            ->assertSee('346.00')
            ->assertSee('51.90')
            ->assertSee('397.90')
            ->assertSee('PROCEDER CON EL PAGO')
            ->assertSee(route('checkout.index'))
            ->assertSee('Enviar pedido al vendedor')
            ->assertSee('Estimación de cuotas')
            ->assertSee('3 cuotas')
            ->assertSee('132.63');
    }

    public function test_cart_quantity_can_be_incremented(): void
    {
        $producto = $this->crearProducto();

        $this->withSession(['carrito' => $this->sesionCarrito($producto)])
            ->post(route('carrito.update', $producto), ['accion' => 'incrementar'])
            ->assertRedirect(route('carrito.index'));

        $this->assertSame(2, session('carrito')[$producto->id_producto]['cantidad']);
    }

    public function test_cart_cannot_increment_beyond_available_stock(): void
    {
        $producto = $this->crearProducto(['stock_actual' => 2]);

        $this->withSession(['carrito' => $this->sesionCarrito($producto, 2)])
            ->post(route('carrito.update', $producto), ['accion' => 'incrementar'])
            ->assertRedirect(route('carrito.index'))
            ->assertSessionHas('error');

        $this->assertSame(2, session('carrito')[$producto->id_producto]['cantidad']);
    }

    public function test_decrementing_the_last_unit_removes_the_product(): void
    {
        $producto = $this->crearProducto();

        $this->withSession(['carrito' => $this->sesionCarrito($producto)])
            ->post(route('carrito.update', $producto), ['accion' => 'decrementar'])
            ->assertRedirect(route('carrito.index'))
            ->assertSessionHas('success');

        $this->assertArrayNotHasKey($producto->id_producto, session('carrito', []));
    }

    public function test_cart_clamps_quantity_to_current_stock(): void
    {
        $producto = $this->crearProducto(['stock_actual' => 2]);

        $this->withSession(['carrito' => $this->sesionCarrito($producto, 5)])
            ->get(route('carrito.index'))
            ->assertOk()
            ->assertSee('Algunos productos se ajustaron')
            ->assertSee('2');

        $this->assertSame(2, session('carrito')[$producto->id_producto]['cantidad']);
    }

    public function test_checkout_requires_an_authenticated_client(): void
    {
        $producto = $this->crearProducto();

        $this->withSession(['carrito' => $this->sesionCarrito($producto)])
            ->post(route('carrito.checkout'), ['accion' => 'pagar'])
            ->assertRedirect(route('login'));
    }

    public function test_pay_action_redirects_to_checkout_form(): void
    {
        $cliente = $this->crearCliente();
        $producto = $this->crearProducto();

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => $this->sesionCarrito($producto)])
            ->post(route('carrito.checkout'), ['accion' => 'pagar'])
            ->assertRedirect(route('checkout.index'));

        $this->assertSame(0, PedidoWeb::count());
        $this->assertNotNull(session('carrito'));
    }

    public function test_checkout_can_send_the_order_to_the_vendor(): void
    {
        Mail::fake();
        $cliente = $this->crearCliente();
        $producto = $this->crearProducto();

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => $this->sesionCarrito($producto)])
            ->post(route('carrito.checkout'), ['accion' => 'vendedor'])
            ->assertRedirect('/')
            ->assertSessionHas('success');

        $pedido = PedidoWeb::firstOrFail();

        $this->assertSame('Coordinar con vendedor', $pedido->metodo_pago);
        $this->assertSame('397.90', $pedido->total);
        $this->assertNull(session('carrito'));
        Mail::assertSent(PedidoWebComprobante::class, fn (PedidoWebComprobante $mail) => $mail->hasTo('carrito@test.com'));
    }
}
