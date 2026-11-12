<?php

namespace App\Http\Controllers;

use App\Mail\PedidoWebComprobante;
use App\Models\DetallePedidoWeb;
use App\Models\PedidoWeb;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

class CarritoController extends Controller
{
    private const IVA = 0.15;

    private const PROVINCIAS = [
        'Azuay', 'Bolívar', 'Cañar', 'Carchi', 'Chimborazo', 'Cotopaxi', 'El Oro',
        'Esmeraldas', 'Galápagos', 'Guayas', 'Imbabura', 'Loja', 'Los Ríos',
        'Manabí', 'Morona Santiago', 'Napo', 'Orellana', 'Pastaza', 'Pichincha',
        'Santa Elena', 'Santo Domingo de los Tsáchilas', 'Sucumbíos',
        'Tungurahua', 'Zamora Chinchipe',
    ];

    private const METODOS_ENTREGA = [
        'servientrega' => 'Envío por Servientrega',
        'retiro' => 'Retiro en oficina (CDA System)',
    ];

    private const METODOS_PAGO = [
        'transferencia' => 'Transferencia Bancaria',
        'tarjeta' => 'Tarjeta de Crédito',
        'deuna' => 'Deuna',
    ];

    // Mostrar el carrito
    public function index()
    {
        $datos = $this->construirCarrito();

        return view('tienda.carrito', $datos);
    }

    // Añadir producto al carrito
    public function add($id)
    {
        $id = (int) $id;
        $producto = Producto::findOrFail($id);
        $carrito = session()->get('carrito', []);

        if (! $producto->activo) {
            return redirect()->back()->with('error', 'El producto no está disponible.');
        }

        $enCarrito = $carrito[$id]['cantidad'] ?? 0;

        if ($producto->stock_actual <= $enCarrito) {
            return redirect()->back()->with('error', 'No hay más stock disponible de este producto.');
        }

        // Si el producto ya está en el carrito, sumamos la cantidad
        if (isset($carrito[$id])) {
            $carrito[$id]['cantidad']++;
        } else {
            // Si no, lo agregamos
            $carrito[$id] = [
                'nombre' => $producto->nombre,
                'sku' => $producto->sku,
                'cantidad' => 1,
                'precio' => $producto->precio_venta,
                'imagen' => $producto->imagen_principal,
            ];
        }

        session()->put('carrito', $carrito);

        return redirect()->back()->with('success', 'Producto añadido al carrito');
    }

    // Cambiar la cantidad de un producto (+ / -)
    public function update(Request $request, $id)
    {
        $id = (int) $id;
        $datos = $request->validate([
            'accion' => 'required|in:incrementar,decrementar,establecer',
            'cantidad' => 'nullable|integer|min:1|max:999',
        ]);

        $producto = Producto::findOrFail($id);
        $carrito = session()->get('carrito', []);

        if (! isset($carrito[$id])) {
            return redirect()->route('carrito.index')
                ->with('error', 'El producto no está en tu carrito.');
        }

        if (! $producto->activo) {
            unset($carrito[$id]);
            session()->put('carrito', $carrito);

            return redirect()->route('carrito.index')
                ->with('error', 'El producto ya no está disponible y fue retirado del carrito.');
        }

        $actual = (int) ($carrito[$id]['cantidad'] ?? 1);

        $nueva = match ($datos['accion']) {
            'incrementar' => $actual + 1,
            'decrementar' => $actual - 1,
            default => (int) ($datos['cantidad'] ?? $actual),
        };

        if ($nueva < 1) {
            unset($carrito[$id]);
            session()->put('carrito', $carrito);

            return redirect()->route('carrito.index')
                ->with('success', 'Producto eliminado del carrito.');
        }

        if ($nueva > (int) $producto->stock_actual) {
            return redirect()->route('carrito.index')
                ->with('error', 'Solo hay '.$producto->stock_actual.' unidades disponibles de este producto.');
        }

        $carrito[$id]['cantidad'] = $nueva;
        $carrito[$id]['precio'] = (float) $producto->precio_venta;
        session()->put('carrito', $carrito);

        return redirect()->route('carrito.index')->with('success', 'Cantidad actualizada.');
    }

    // Eliminar producto
    public function remove($id)
    {
        $id = (int) $id;
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            unset($carrito[$id]);
            session()->put('carrito', $carrito);
        }

        return redirect()->back()->with('success', 'Producto eliminado');
    }

    // Mostrar el formulario de facturación y envío
    public function checkoutForm()
    {
        $cliente = Auth::guard('cliente')->user();

        if (! $cliente) {
            return redirect()->route('login')
                ->with('error', 'Inicia sesión para completar tu compra.');
        }

        $carrito = $this->construirCarrito();

        if (empty($carrito['lineas'])) {
            return redirect()->route('carrito.index')
                ->with('error', 'Tu carrito está vacío.');
        }

        return view('tienda.checkout', array_merge($carrito, [
            'cliente' => $cliente,
            'provincias' => self::PROVINCIAS,
            'metodosEntrega' => self::METODOS_ENTREGA,
            'metodosPago' => self::METODOS_PAGO,
        ]));
    }

    // Registrar el pedido con los datos de facturación y envío
    public function checkoutStore(Request $request)
    {
        $cliente = Auth::guard('cliente')->user();

        if (! $cliente) {
            return redirect()->route('login')
                ->with('error', 'Inicia sesión para completar tu compra.');
        }

        $datos = $request->validate([
            'nombre_contacto' => 'required|string|max:150',
            'dni_ruc' => 'nullable|string|max:20',
            'empresa' => 'nullable|string|max:150',
            'telefono_contacto' => 'required|string|max:20',
            'email_contacto' => 'required|email|max:150',
            'provincia' => ['required', 'string', 'max:100', Rule::in(self::PROVINCIAS)],
            'ciudad' => 'required|string|max:100',
            'direccion_envio' => 'required|string|max:120',
            'referencia_envio' => 'nullable|string|max:100',
            'informacion_adicional' => 'nullable|string|max:100',
            'metodo_entrega' => ['required', Rule::in(array_keys(self::METODOS_ENTREGA))],
            'metodo_pago' => ['required', Rule::in(array_keys(self::METODOS_PAGO))],
        ]);

        $carrito = $this->construirCarrito();

        if (empty($carrito['lineas'])) {
            return redirect()->route('carrito.index')
                ->with('error', 'Tu carrito está vacío.');
        }

        $pedido = $this->registrarPedido($carrito, $cliente->id_cliente, [
            'metodo_pago' => self::METODOS_PAGO[$datos['metodo_pago']],
            'metodo_entrega' => self::METODOS_ENTREGA[$datos['metodo_entrega']],
            'nombre_contacto' => $datos['nombre_contacto'],
            'dni_ruc' => $datos['dni_ruc'] ?? null,
            'empresa' => $datos['empresa'] ?? null,
            'telefono_contacto' => $datos['telefono_contacto'],
            'email_contacto' => $datos['email_contacto'],
            'provincia' => $datos['provincia'],
            'ciudad' => $datos['ciudad'],
            'direccion_envio' => $datos['direccion_envio'],
            'referencia_envio' => $datos['referencia_envio'] ?? null,
            'informacion_adicional' => $datos['informacion_adicional'] ?? null,
        ]);
        $correoEnviado = $this->enviarComprobante($pedido);

        session()->forget('carrito');

        $mensaje = 'Pedido #'.$pedido->id_pedido.' registrado. Se procesará cuando se confirme el pago.';
        if (! $correoEnviado) {
            $mensaje .= ' No pudimos enviar el comprobante; verifica que el correo sea correcto o contacta a la tienda.';
        }

        return redirect('/')->with(
            'success',
            $mensaje
        );
    }

    // Enviar el pedido directamente al vendedor desde el carrito
    public function checkout(Request $request)
    {
        $cliente = Auth::guard('cliente')->user();

        if (! $cliente) {
            return redirect()->route('login')
                ->with('error', 'Inicia sesión para completar tu compra.');
        }

        if ($request->input('accion') !== 'vendedor') {
            return redirect()->route('checkout.index');
        }

        $carrito = $this->construirCarrito();

        if (empty($carrito['lineas'])) {
            return redirect()->route('carrito.index')
                ->with('error', 'Tu carrito está vacío.');
        }

        $pedido = $this->registrarPedido($carrito, $cliente->id_cliente, [
            'metodo_pago' => 'Coordinar con vendedor',
            'metodo_entrega' => 'Por coordinar',
        ]);
        $correoEnviado = $this->enviarComprobante($pedido);

        session()->forget('carrito');

        $mensaje = 'Pedido #'.$pedido->id_pedido.' enviado al vendedor. Te contactaremos para coordinar la entrega.';
        if (! $correoEnviado) {
            $mensaje .= ' No pudimos enviar el comprobante; verifica que tu correo esté registrado.';
        }

        return redirect('/')->with(
            'success',
            $mensaje
        );
    }

    private function enviarComprobante(PedidoWeb $pedido): bool
    {
        $pedido->load(['cliente', 'detalles.producto']);
        $correo = $pedido->email_contacto ?: $pedido->cliente?->email;

        if (! $correo) {
            Log::warning('No se envió comprobante de pedido porque el cliente no tiene correo.', [
                'id_pedido' => $pedido->id_pedido,
            ]);

            return false;
        }

        try {
            Mail::to($correo)->send(new PedidoWebComprobante($pedido));

            return true;
        } catch (Throwable $exception) {
            Log::error('No se pudo enviar el comprobante del pedido.', [
                'id_pedido' => $pedido->id_pedido,
                'correo' => $correo,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    // Crea el pedido y sus líneas dentro de una transacción
    private function registrarPedido(array $carrito, int $clienteId, array $datos): PedidoWeb
    {
        return DB::transaction(function () use ($carrito, $clienteId, $datos) {
            $pedido = PedidoWeb::create(array_merge([
                'id_cliente' => $clienteId,
                'fecha_pedido' => now(),
                'estado_pedido' => 'Pendiente',
                'subtotal' => $carrito['subtotal'],
                'costo_envio' => 0,
                'total' => $carrito['total'],
            ], $datos));

            foreach ($carrito['lineas'] as $linea) {
                DetallePedidoWeb::create([
                    'id_pedido' => $pedido->id_pedido,
                    'id_producto' => $linea['producto']->id_producto,
                    'cantidad' => $linea['cantidad'],
                    'precio_unitario' => $linea['precio'],
                ]);
            }

            return $pedido;
        });
    }

    // Recalcula el carrito con precios y stock actuales, aplicando el IVA
    private function construirCarrito(): array
    {
        $carrito = session()->get('carrito', []);
        $productos = Producto::whereIn('id_producto', array_keys($carrito))->get()->keyBy('id_producto');

        $lineas = [];
        $subtotal = 0.0;
        $cambios = false;

        foreach ($carrito as $id => $item) {
            $producto = $productos->get($id);

            if (! $producto || ! $producto->activo || (int) $producto->stock_actual < 1) {
                unset($carrito[$id]);
                $cambios = true;

                continue;
            }

            $cantidad = max(1, (int) ($item['cantidad'] ?? 1));

            if ($cantidad > (int) $producto->stock_actual) {
                $cantidad = (int) $producto->stock_actual;
                $cambios = true;
            }

            $carrito[$id]['cantidad'] = $cantidad;

            $precio = (float) $producto->precio_venta;
            $importe = round($precio * $cantidad, 2);
            $subtotal += $importe;

            $lineas[$id] = [
                'producto' => $producto,
                'cantidad' => $cantidad,
                'precio' => $precio,
                'importe' => $importe,
            ];
        }

        if ($cambios) {
            session()->put('carrito', $carrito);
        }

        $subtotal = round($subtotal, 2);
        $iva = round($subtotal * self::IVA, 2);
        $total = round($subtotal + $iva, 2);

        return [
            'lineas' => $lineas,
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $total,
            'cantidadTotal' => array_sum(array_column($lineas, 'cantidad')),
            'ajustado' => $cambios,
        ];
    }
}
