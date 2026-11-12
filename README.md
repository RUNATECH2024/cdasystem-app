# CDASystem Backend

Backend de **CDASystem** construido con **Laravel 12** y **PHP 8.2**. Cubre
la tienda web, el catálogo, el carrito, las ventas de mostrador, el taller de servicio
técnico y el inventario.

## Requisitos

- PHP 8.2+
- Composer 2
- PostgreSQL 12+
- Node.js / npm (para los assets con Vite)

## Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
php artisan migrate
php artisan db:seed
php artisan serve
```

## Base de datos

La aplicación trabaja sobre PostgreSQL (base `cdasystem`). El esquema de dominio se
versiona con migraciones que son **idempotentes**: cada `up()` verifica
`Schema::hasTable(...)` antes de crear la tabla, por lo que pueden ejecutarse tanto en
una base nueva como en la base ya existente sin recrear ni borrar datos.

> Importante: la base original es **externa/previa**. Las migraciones sirven para
> reconstruirla desde cero en un entorno nuevo. Sobre la base actual solo registran su
> ejecución y no modifican los datos existentes.

Componentes principales del modelo:

- Catálogo: `categorias`, `subcategorias`, `productos`, `atributos_producto`.
- Usuarios: `usuarios` (personal, auth API) y `clientes` (tienda, guard web).
- Inventario: `almacenes`, `proveedores`, `inventario_almacen`, `movimientos_inventario`.
- Ventas: `ventas`, `detalle_ventas`, `abonos`.
- Compras: `compras`, `detalle_compras`.
- Taller: `equipos_cliente`, `ordenes_servicio`, `detalle_repuestos_os`, `servicios`.
- Pedidos web: `pedidos_web`, `detalle_pedidos_web`, `garantias`.

### Seeders

`DatabaseSeeder` carga datos base de catálogo y usuarios del personal:

```bash
php artisan db:seed
```

Usuarios sembrados: `admin`, `carlos`, `pedro`. La contraseña se toma de la variable
`SEED_USER_PASSWORD` (por defecto `admin123` en local). Los seeders usan
`firstOrCreate`, así que no duplican registros al re-ejecutarse.

Categorías sembradas: Computadoras, Componentes, Periféricos, Monitores, Impresión,
Móviles, TV & Video, Audio, Adaptadores & Cables, Redes, Energía, Servidores, Software,
Hogar y Equipos Gaming.

## Autenticación

- **API (personal)**: Laravel Sanctum con tokens Bearer. Login en `POST /api/login`.
  Los tokens expiran a los 7 días por defecto (`SANCTUM_EXPIRATION`, en minutos).
- **Tienda (clientes)**: sesión web con el guard `cliente` (`/login`, `/register`) y la
  cookie `laravel_session`.
- **Administración (personal)**: sesión web con el guard `admin` sobre la tabla `usuarios`
  (`/admin/login`) y la cookie separada `admin_session`, para que una sesión de cliente
  no pueda mezclarse con la del panel. Usuario sembrado: `admin` / `admin123` (cambiar en
  producción).

Los guards de cliente y administrador tienen sesiones independientes. Al iniciar sesión
se regenera el identificador de sesión y al cerrar sesión se invalida y se renueva el
token CSRF. Las cuentas desactivadas se expulsan en la siguiente petición web y reciben
`403` en la API.

## Rutas de la tienda web

- `GET /` y `GET /categorias/{id}` — catálogo y subcategorías de una categoría.
- `GET /categoria/{id}` — productos de una subcategoría.
- `GET /producto/{id}` — ficha del producto con características, garantía y vencimiento.
- `GET /buscar?q=...` — buscador de productos.
- `GET /directorio-marcas` — stock activo agrupado por marca.
- `GET /carrito`, `POST /carrito/add/{id}`, `POST /carrito/update/{id}`, `POST /carrito/remove/{id}` — carrito en sesión con control de cantidades, stock e IVA (15%).
- `GET /checkout` — formulario de facturación y envío (datos del cliente, provincia, ciudad, dirección, entrega y pago).
- `POST /checkout` — registra el pedido (`pedidos_web`) con IVA incluido y los datos de facturación/envío; queda pendiente hasta confirmar el pago.
- `POST /carrito/checkout` — envío rápido al vendedor (`accion=vendedor`) sin pasar por el formulario. Requiere sesión de cliente.

## Panel de administración

Requiere un usuario con rol `Admin` de la tabla `usuarios`. Todas las acciones de
escritura y las descargas de respaldo quedan registradas en `admin_audit_logs`.

- `GET /admin/login` — formulario de acceso.
- `POST /admin/login` — inicia sesión (limitado a 5 intentos por minuto).
- `POST /admin/logout` — cierra sesión.
- `GET /admin` — dashboard.
- `GET /admin/productos` — listado con búsqueda, filtros y acciones rápidas.
- `GET /admin/productos/crear`, `POST /admin/productos` — alta de producto.
- `GET /admin/productos/{id}/editar`, `PUT /admin/productos/{id}` — edición con
  atributos y campos por categoría.
- `PATCH /admin/productos/{id}/estado` — activa o desactiva el producto.
- `PATCH /admin/productos/{id}/destacado` — marca o desmarca como destacado.
- `POST /admin/productos/{id}/marca` — actualiza solo la marca.
- `GET /admin/productos/{id}/ficha-pdf` — ficha técnica del producto en PDF.
- `GET /admin/pedidos`, `GET /admin/pedidos/{id}` — pedidos web y su detalle.
- `PATCH /admin/pedidos/{id}/atendido` — marca un pedido como atendido.
- `GET /admin/ordenes-taller` — órdenes de taller, con creación, edición, PDF y borrado.
- `GET /admin/clientes`, `GET /admin/clientes/{id}`, `GET /admin/clientes/{id}/editar` — listado
  con búsqueda, detalle con equipos e historial, y edición de clientes.
- `PUT /admin/clientes/{id}`, `PATCH /admin/clientes/{id}/estado`, `DELETE /admin/clientes/{id}` —
  actualización, activación/desactivación y borrado seguro de clientes sin historial.
- `GET /admin/clientes/crear`, `POST /admin/clientes` — alta de cliente y, opcionalmente,
  su primer equipo para continuar con una orden de taller.
- `GET /admin/proveedores`, `GET /admin/proveedores/{id}`, `GET /admin/proveedores/{id}/editar` —
  listado con búsqueda, detalle con compras y edición de proveedores.
- `POST /admin/proveedores`, `PUT /admin/proveedores/{id}`, `PATCH /admin/proveedores/{id}/estado` —
  alta, actualización y activación/desactivación de proveedores.
- `DELETE /admin/proveedores/{id}` — borrado seguro de proveedores sin compras ni saldo pendiente.
- `GET /admin/compras` — listado de compras con búsqueda y filtros por estado y fechas.
- `GET /admin/compras/crear`, `POST /admin/compras` — alta de compra con detalle de productos,
  actualización automática de stock, costo del producto y saldo del proveedor.
- `GET /admin/compras/{id}` — detalle de compra, productos, abonos y saldo pendiente.
- `POST /admin/compras/{id}/abonos` — registra un abono y recalcula estado y saldo del proveedor.
- `GET /admin/compras/{id}/pdf` — comprobante de compra en PDF.
- `GET /admin/reportes` — inventario, ventas, clientes, proveedores, compras, pedidos web y taller
  con filtro de fechas.
- `GET /admin/reportes/exportar?tipo=inventario|ventas|clientes|proveedores` — descarga CSV.
- `GET /admin/reportes/pdf?tipo=inventario|ventas|clientes|proveedores` — reporte en PDF.
- `GET /admin/usuarios`, `GET /admin/usuarios/crear`, `POST /admin/usuarios` — alta de personal.
- `GET /admin/auditoria` — eventos de auditoría.
- `GET /admin/respaldos`, `POST /admin/respaldos` — listado y creación de respaldos PostgreSQL.
- `GET /admin/respaldos/{nombre}/descargar` — descarga de un respaldo.

## Endpoints de la API

Públicos (límite de 60 peticiones por minuto):

- `GET /api/catalogo/menu`
- `GET /api/catalogo/productos/{id_subcategoria}`
- `GET /api/catalogo/buscar?q=...`
- `POST /api/login` (limitado a 5 intentos por minuto)

Protegidos (`auth:sanctum`, cuenta activa y 120 peticiones por minuto):

- `POST /api/logout` — revoca el token actual.
- `POST /api/productos` — solo rol `Admin`. Alta de producto (`nombre` y
  `precio_venta` obligatorios; `sku` y `codigo_barras` únicos; `marca` opcional;
  admite `atributos[]` con `nombre_atributo` y `valor_atributo`).
- `POST /api/ventas` — roles `Admin` y `Vendedor`.
- `POST /api/taller/ordenes` — roles `Admin` y `Tecnico`. Si el usuario es técnico
  y no indica `id_tecnico`, la orden se le asigna automáticamente.
- `POST /api/taller/ordenes/{id}/repuestos` — roles `Admin` y `Tecnico`.

Cualquier otro rol recibe `403`. La API usa CORS restringido a `APP_URL` y a los
orígenes indicados en `CORS_ALLOWED_ORIGINS` (separados por coma).

## Tests

```bash
php artisan test
```

También se puede ejecutar la suite con SQLite en memoria:

```powershell
$env:DB_CONNECTION='sqlite'; $env:DB_DATABASE=':memory:'; php artisan test
```

## Seguridad

- No versionar `.env` (contiene `APP_KEY` y credenciales de la base).
- Cambiar la contraseña por defecto del personal sembrado en entornos productivos.
- Usar HTTPS en producción. Con `APP_ENV=production` la cookie de sesión se marca
  `Secure` automáticamente; también se puede forzar con `SESSION_SECURE_COOKIE=true`.
- `SESSION_ENCRYPT=true` cifra los datos de sesión. Las cookies son `HttpOnly` y
  `SameSite=Lax`.
- Los logins tienen `throttle`: cliente 5/min, administrador 5/min y API 5/min.
  La API pública permite 60 peticiones/min y la protegida 120/min. El registro de
  clientes permite 10 intentos/min y el checkout 30/min.
- Los guards `admin` y `cliente` usan cookies de sesión distintas.
- Se aplican cabeceras de seguridad globales: CSP, `X-Frame-Options`,
  `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, COOP/CORP,
  HSTS en HTTPS y `Cache-Control: no-store` en rutas sensibles.
- Los usuarios inactivos no pueden iniciar sesión ni mantener una sesión abierta.
- La API aplica autorización por rol (`role:Admin`, `role:Admin,Vendedor`,
  `role:Admin,Tecnico`) y los tokens Sanctum expiran a los 7 días.
- Las contraseñas se guardan con `Hash` (bcrypt/argon). La comparación de login usa
  verificación de tiempo constante, incluso cuando el usuario no existe.
- Los CSV exportados neutralizan fórmulas de hoja de cálculo (`=`, `+`, `-`, `@`)
  para evitar inyección de fórmulas.
- Las imágenes de productos se validan como imagen, con tamaño y dimensiones máximas;
  `public/productos/.htaccess` impide ejecutar PHP en esa carpeta.
- Los respaldos solo se descargan desde `storage/app/private/backups` y el nombre se
  valida para evitar traversal. Las descargas y acciones administrativas se auditan.
- CORS está restringido por configuración; no se reflejan orígenes desconocidos.
