<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedido recibido</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <h1 style="font-size: 22px;">Recibimos tu pedido</h1>
    <p>Hola {{ $pedido->nombre_contacto ?: $pedido->cliente?->nombre ?: 'cliente' }},</p>
    <p>Tu pedido <strong>#{{ $pedido->id_pedido }}</strong> fue registrado el {{ $pedido->fecha_pedido?->format('d/m/Y H:i') }}.</p>
    <p>Estado: <strong>{{ $pedido->estado_pedido }}</strong><br>
       Total: <strong>${{ number_format((float) $pedido->total, 2) }}</strong><br>
       Método de pago: {{ $pedido->metodo_pago ?: 'Por coordinar' }}</p>
    <p>Adjuntamos el comprobante de tu pedido en formato PDF. Te contactaremos para coordinar los siguientes pasos.</p>
    <p>Gracias por tu compra,<br>{{ config('app.name') }}</p>
</body>
</html>