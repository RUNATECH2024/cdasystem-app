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

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function crearCliente(): Cliente
    {
        return Cliente::create([
            'nombre' => 'AGUALONGO NARANJO CRISTIAN DAVID',
            'dni_ruc' => '0201578655001',
            'telefono' => '0980821599',
            'email' => 'crisheadbanger333@gmail.com',
            'direccion' => 'SIMON BOLIVAR MARGARITA GORDILLO SN',
            'password' => Hash::make('secreto123'),
            'activo' => true,
        ]);
    }

    private function crearProducto(array $overrides = []): Producto
    {
        return Producto::create(array_merge([
            'nombre' => 'NOT. LENOVO IdeaPad Intel N150 8GB 128GB 15.6Pulg.',
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

    private function datosFormulario(array $overrides = []): array
    {
        return array_merge([
            'nombre_contacto' => 'AGUALONGO NARANJO CRISTIAN DAVID',
            'dni_ruc' => '0201578655001',
            'empresa' => '',
            'telefono_contacto' => '0980821599',
            'email_contacto' => 'crisheadbanger333@gmail.com',
            'provincia' => 'Bolívar',
            'ciudad' => 'Echeandia',
            'direccion_envio' => 'SIMON BOLIVAR MARGARITA GORDILLO SN',
            'referencia_envio' => 'Frente al parque central',
            'informacion_adicional' => '',
            'metodo_entrega' => 'servientrega',
            'metodo_pago' => 'transferencia',
        ], $overrides);
    }

    public function test_guest_cannot_access_checkout(): void
    {
        $this->get(route('checkout.index'))->assertRedirect(route('login'));
    }

    public function test_checkout_requires_products_in_the_cart(): void
    {
        $cliente = $this->crearCliente();

        $this->actingAs($cliente, 'cliente')
            ->get(route('checkout.index'))
            ->assertRedirect(route('carrito.index'))
            ->assertSessionHas('error');
    }

    public function test_checkout_page_shows_billing_shipping_and_summary(): void
    {
        $cliente = $this->crearCliente();
        $producto = $this->crearProducto();

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => $this->sesionCarrito($producto)])
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Facturación y envío')
            ->assertSee('Datos de facturación')
            ->assertSee('AGUALONGO NARANJO CRISTIAN DAVID')
            ->assertSee('0201578655001')
            ->assertSee('0980821599')
            ->assertSee('crisheadbanger333@gmail.com')
            ->assertSee('Dirección de envío')
            ->assertSee('Método de Entrega')
            ->assertSee('Envío por Servientrega')
            ->assertSee('Retiro en oficina (CDA System)')
            ->assertSee('Método de Pago')
            ->assertSee('Transferencia Bancaria')
            ->assertSee('Tarjeta de Crédito')
            ->assertSee('Deuna')
            ->assertSee('COMLEN83L7002KU')
            ->assertSee('346.00')
            ->assertSee('51.90')
            ->assertSee('397.90')
            ->assertSee('FINALIZAR COMPRA');
    }

    public function test_finalizing_purchase_creates_order_with_shipping_data(): void
    {
        Mail::fake();
        $cliente = $this->crearCliente();
        $producto = $this->crearProducto();

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => $this->sesionCarrito($producto)])
            ->post(route('checkout.store'), $this->datosFormulario())
            ->assertRedirect('/')
            ->assertSessionHas('success');

        $pedido = PedidoWeb::firstOrFail();

        Mail::assertSent(PedidoWebComprobante::class, function (PedidoWebComprobante $mail) {
            $adjuntos = $mail->attachments();

            if (! $mail->hasTo('crisheadbanger333@gmail.com') || count($adjuntos) !== 1) {
                return false;
            }

            $pdf = $adjuntos[0]->attachWith(
                fn ($path) => $path,
                fn ($data) => $data()
            );

            return $adjuntos[0]->as === 'comprobante_pedido_'.$mail->pedido->id_pedido.'.pdf'
                && str_starts_with($pdf, '%PDF');
        });

        $this->assertSame('346.00', $pedido->subtotal);
        $this->assertSame('397.90', $pedido->total);
        $this->assertSame('Pendiente', $pedido->estado_pedido);
        $this->assertSame('Transferencia Bancaria', $pedido->metodo_pago);
        $this->assertSame('Envío por Servientrega', $pedido->metodo_entrega);
        $this->assertSame('AGUALONGO NARANJO CRISTIAN DAVID', $pedido->nombre_contacto);
        $this->assertSame('0201578655001', $pedido->dni_ruc);
        $this->assertSame('0980821599', $pedido->telefono_contacto);
        $this->assertSame('crisheadbanger333@gmail.com', $pedido->email_contacto);
        $this->assertSame('Bolívar', $pedido->provincia);
        $this->assertSame('Echeandia', $pedido->ciudad);
        $this->assertSame('SIMON BOLIVAR MARGARITA GORDILLO SN', $pedido->direccion_envio);
        $this->assertSame('Frente al parque central', $pedido->referencia_envio);

        $this->assertDatabaseHas('detalle_pedidos_web', [
            'id_pedido' => $pedido->id_pedido,
            'id_producto' => $producto->id_producto,
            'cantidad' => 1,
            'precio_unitario' => 346,
        ]);

        $this->assertNull(session('carrito'));
    }

    public function test_checkout_can_use_office_pickup_and_deuna(): void
    {
        Mail::fake();
        $cliente = $this->crearCliente();
        $producto = $this->crearProducto();

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => $this->sesionCarrito($producto)])
            ->post(route('checkout.store'), $this->datosFormulario([
                'metodo_entrega' => 'retiro',
                'metodo_pago' => 'deuna',
            ]))
            ->assertRedirect('/');

        $pedido = PedidoWeb::firstOrFail();

        $this->assertSame('Retiro en oficina (CDA System)', $pedido->metodo_entrega);
        $this->assertSame('Deuna', $pedido->metodo_pago);
        Mail::assertSent(PedidoWebComprobante::class, fn (PedidoWebComprobante $mail) => $mail->hasTo('crisheadbanger333@gmail.com'));
    }

    public function test_checkout_validates_required_fields(): void
    {
        $cliente = $this->crearCliente();
        $producto = $this->crearProducto();

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => $this->sesionCarrito($producto)])
            ->post(route('checkout.store'), [])
            ->assertSessionHasErrors([
                'nombre_contacto', 'telefono_contacto', 'email_contacto', 'provincia',
                'ciudad', 'direccion_envio', 'metodo_entrega', 'metodo_pago',
            ]);

        $this->assertSame(0, PedidoWeb::count());
    }

    public function test_checkout_rejects_unknown_delivery_or_payment_methods(): void
    {
        $cliente = $this->crearCliente();
        $producto = $this->crearProducto();

        $this->actingAs($cliente, 'cliente')
            ->withSession(['carrito' => $this->sesionCarrito($producto)])
            ->post(route('checkout.store'), $this->datosFormulario([
                'metodo_entrega' => 'uber',
                'metodo_pago' => 'bitcoin',
            ]))
            ->assertSessionHasErrors(['metodo_entrega', 'metodo_pago']);

        $this->assertSame(0, PedidoWeb::count());
    }
}
