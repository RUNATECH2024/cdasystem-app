<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Subcategoria;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProductoTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdmin(): Usuario
    {
        return Usuario::create([
            'nombre' => 'Administrador',
            'usuario' => 'admin',
            'password_hash' => Hash::make('admin123'),
            'rol' => 'Admin',
            'activo' => true,
        ]);
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_can_login_with_credentials(): void
    {
        $admin = $this->crearAdmin();

        $this->post('/admin/login', [
            'usuario' => 'admin',
            'password' => 'admin123',
        ])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_login_fails_with_invalid_password(): void
    {
        $this->crearAdmin();

        $this->post('/admin/login', [
            'usuario' => 'admin',
            'password' => 'incorrecta',
        ])->assertSessionHasErrors('usuario');

        $this->assertGuest('admin');
    }

    public function test_non_admin_user_cannot_login_to_admin_panel(): void
    {
        Usuario::create([
            'nombre' => 'Vendedor',
            'usuario' => 'vendedor',
            'password_hash' => Hash::make('vendedor123'),
            'rol' => 'Vendedor',
            'activo' => true,
        ]);

        $this->post('/admin/login', [
            'usuario' => 'vendedor',
            'password' => 'vendedor123',
        ])->assertSessionHasErrors('usuario');

        $this->assertGuest('admin');
    }

    public function test_authenticated_admin_can_create_a_product(): void
    {
        $admin = $this->crearAdmin();

        $categoria = Categoria::create(['nombre' => 'Monitores', 'activo' => true]);
        $subcategoria = Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'GAMING',
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->post('/admin/productos', [
                'nombre' => 'Monitor 24 pulgadas',
                'sku' => 'MON-001',
                'id_subcategoria' => $subcategoria->id_subcategoria,
                'marca' => 'Samsung',
                'precio_compra' => 100,
                'precio_venta' => 180,
                'stock_actual' => 12,
                'stock_minimo' => 3,
                'activo' => '1',
            ])
            ->assertRedirect(route('admin.productos.index'));

        $this->assertDatabaseHas('productos', [
            'sku' => 'MON-001',
            'nombre' => 'Monitor 24 pulgadas',
            'stock_actual' => 12,
            'activo' => true,
        ]);
    }

    public function test_authenticated_admin_can_update_product_brand(): void
    {
        $admin = $this->crearAdmin();

        $producto = Producto::create([
            'nombre' => 'Producto sin marca',
            'precio_venta' => 50,
            'stock_actual' => 1,
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.productos.index'))
            ->post(route('admin.productos.marca', $producto), ['marca' => 'Lenovo'])
            ->assertRedirect(route('admin.productos.index'));

        $this->assertDatabaseHas('productos', [
            'id_producto' => $producto->id_producto,
            'marca' => 'Lenovo',
        ]);
    }

    public function test_admin_can_see_product_form_with_category_and_subcategory_pickers(): void
    {
        $admin = $this->crearAdmin();

        $categoria = Categoria::create(['nombre' => 'Computadoras', 'activo' => true]);
        Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'LAPTOPS',
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.productos.create'))
            ->assertOk()
            ->assertSee('Categoría')
            ->assertSee('Computadoras')
            ->assertSee('LAPTOPS')
            ->assertSee('campo-vencimiento', false)
            ->assertSee('seccion-detalles', false)
            ->assertSee('seccion-atributos', false);
    }

    public function test_form_rules_change_according_to_category(): void
    {
        $admin = $this->crearAdmin();

        $software = Categoria::create(['nombre' => 'Software', 'activo' => true]);
        $computadoras = Categoria::create(['nombre' => 'Computadoras', 'activo' => true]);
        $energia = Categoria::create(['nombre' => 'Energía', 'activo' => true]);

        $config = $this->actingAs($admin, 'admin')
            ->get(route('admin.productos.create'))
            ->assertOk()
            ->viewData('configPorCategoria');

        $this->assertFalse($config[$software->id_categoria]['garantia']);
        $this->assertFalse($config[$software->id_categoria]['atributos']);
        $this->assertContains('tipo_licencia', array_column($config[$software->id_categoria]['campos'], 'name'));

        $this->assertTrue($config[$computadoras->id_categoria]['garantia']);
        $this->assertTrue($config[$computadoras->id_categoria]['atributos']);
        $this->assertNotEmpty($config[$computadoras->id_categoria]['ejemplos']);

        $this->assertContains('capacidad_va', array_column($config[$energia->id_categoria]['campos'], 'name'));
    }

    public function test_admin_can_save_category_specific_fields(): void
    {
        $admin = $this->crearAdmin();

        $categoria = Categoria::create(['nombre' => 'Software', 'activo' => true]);
        $subcategoria = Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'ANTIVIRUS',
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->post('/admin/productos', [
                'nombre' => 'Antivirus Pro',
                'sku' => 'SW-001',
                'id_subcategoria' => $subcategoria->id_subcategoria,
                'marca' => 'Eset',
                'precio_compra' => 20,
                'precio_venta' => 35,
                'stock_actual' => 10,
                'condicion' => 'Nuevo',
                'campos_categoria' => [
                    'tipo_licencia' => 'Suscripción',
                    'duracion' => '1 año',
                    'equipos' => 3,
                    'formato' => 'Digital',
                ],
            ])
            ->assertRedirect(route('admin.productos.index'));

        $producto = Producto::where('sku', 'SW-001')->firstOrFail();

        $this->assertDatabaseHas('atributos_producto', [
            'id_producto' => $producto->id_producto,
            'nombre_atributo' => 'Tipo de licencia',
            'valor_atributo' => 'Suscripción',
        ]);

        $this->assertDatabaseHas('atributos_producto', [
            'id_producto' => $producto->id_producto,
            'nombre_atributo' => 'Equipos incluidos',
            'valor_atributo' => '3',
        ]);
    }

    public function test_admin_can_save_expiration_date_for_consumables(): void
    {
        $admin = $this->crearAdmin();

        $categoria = Categoria::create(['nombre' => 'Impresión', 'activo' => true]);
        $subcategoria = Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'CONSUMIBLES & SUMINISTROS',
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->post('/admin/productos', [
                'nombre' => 'Tóner HP 85A',
                'sku' => 'TON-85A',
                'id_subcategoria' => $subcategoria->id_subcategoria,
                'marca' => 'HP',
                'precio_compra' => 10,
                'precio_venta' => 20,
                'stock_actual' => 5,
                'condicion' => 'Nuevo',
                'garantia_meses' => 0,
                'fecha_vencimiento' => '2027-01-31',
            ])
            ->assertRedirect(route('admin.productos.index'));

        $producto = Producto::where('sku', 'TON-85A')->firstOrFail();
        $this->assertSame('2027-01-31', $producto->fecha_vencimiento->toDateString());
    }

    public function test_authenticated_admin_can_see_dashboard(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin, 'admin')
            ->get('/admin')
            ->assertOk()
            ->assertSee('Panel de administración')
            ->assertSee('Sin productos registrados.');
    }

    public function test_admin_create_product_validates_required_fields(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin, 'admin')
            ->post('/admin/productos', [])
            ->assertSessionHasErrors(['nombre', 'precio_venta']);
    }

    public function test_admin_can_edit_a_product_with_its_attributes(): void
    {
        $admin = $this->crearAdmin();

        $categoria = Categoria::create(['nombre' => 'Computadoras', 'activo' => true]);
        $subcategoria = Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'LAPTOPS',
            'activo' => true,
        ]);

        $producto = Producto::create([
            'id_subcategoria' => $subcategoria->id_subcategoria,
            'nombre' => 'Laptop Original',
            'sku' => 'LAP-EDIT',
            'marca' => 'Lenovo',
            'precio_compra' => 100,
            'precio_venta' => 150,
            'stock_actual' => 3,
            'stock_minimo' => 1,
            'condicion' => 'Nuevo',
            'garantia_meses' => 12,
            'activo' => true,
        ]);

        $producto->atributos()->create(['nombre_atributo' => 'Procesador', 'valor_atributo' => 'Intel Core i5']);
        $producto->atributos()->create(['nombre_atributo' => 'Uso recomendado', 'valor_atributo' => 'Oficina']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.productos.edit', $producto))
            ->assertOk()
            ->assertSee('Laptop Original')
            ->assertSee('LAP-EDIT')
            ->assertSee('Procesador')
            ->assertSee('Intel Core i5')
            ->assertSee('Oficina')
            ->assertSee('Computadoras');
    }

    public function test_admin_can_update_a_product_and_replace_its_attributes(): void
    {
        $admin = $this->crearAdmin();

        $categoria = Categoria::create(['nombre' => 'Computadoras', 'activo' => true]);
        $subcategoria = Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'LAPTOPS',
            'activo' => true,
        ]);
        $nuevaSubcategoria = Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'DESKTOP',
            'activo' => true,
        ]);

        $producto = Producto::create([
            'id_subcategoria' => $subcategoria->id_subcategoria,
            'nombre' => 'Laptop Original',
            'sku' => 'LAP-EDIT',
            'marca' => 'Lenovo',
            'precio_compra' => 100,
            'precio_venta' => 150,
            'stock_actual' => 3,
            'stock_minimo' => 1,
            'condicion' => 'Nuevo',
            'garantia_meses' => 12,
            'activo' => true,
        ]);

        $producto->atributos()->create(['nombre_atributo' => 'Procesador', 'valor_atributo' => 'Intel Core i5']);
        $producto->atributos()->create(['nombre_atributo' => 'Uso recomendado', 'valor_atributo' => 'Oficina']);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.productos.update', $producto), [
                'nombre' => 'Laptop Actualizada',
                'sku' => 'LAP-EDIT',
                'id_subcategoria' => $nuevaSubcategoria->id_subcategoria,
                'marca' => 'HP',
                'precio_compra' => 120,
                'precio_venta' => 200,
                'stock_actual' => 7,
                'stock_minimo' => 2,
                'condicion' => 'Reacondicionado',
                'garantia_meses' => 6,
                'activo' => '1',
                'destacado' => '1',
                'atributos_nombres' => ['Procesador', 'Memoria RAM'],
                'atributos_valores' => ['Ryzen 5', '16 GB'],
                'campos_categoria' => ['uso' => 'Gaming'],
            ])
            ->assertRedirect(route('admin.productos.index'));

        $producto->refresh();

        $this->assertSame('Laptop Actualizada', $producto->nombre);
        $this->assertSame($nuevaSubcategoria->id_subcategoria, $producto->id_subcategoria);
        $this->assertSame('HP', $producto->marca);
        $this->assertSame(7, $producto->stock_actual);
        $this->assertSame(6, $producto->garantia_meses);
        $this->assertTrue($producto->activo);
        $this->assertTrue($producto->destacado);

        $this->assertDatabaseMissing('atributos_producto', [
            'id_producto' => $producto->id_producto,
            'nombre_atributo' => 'Procesador',
            'valor_atributo' => 'Intel Core i5',
        ]);
        $this->assertDatabaseMissing('atributos_producto', [
            'id_producto' => $producto->id_producto,
            'nombre_atributo' => 'Uso recomendado',
            'valor_atributo' => 'Oficina',
        ]);
        $this->assertDatabaseHas('atributos_producto', [
            'id_producto' => $producto->id_producto,
            'nombre_atributo' => 'Procesador',
            'valor_atributo' => 'Ryzen 5',
        ]);
        $this->assertSame(3, $producto->atributos()->count());
    }

    public function test_admin_can_toggle_product_status_and_featured(): void
    {
        $admin = $this->crearAdmin();

        $producto = Producto::create([
            'nombre' => 'Producto para alternar',
            'precio_venta' => 10,
            'stock_actual' => 1,
            'activo' => true,
            'destacado' => false,
        ]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.productos.estado', $producto))
            ->assertRedirect();

        $this->assertFalse($producto->fresh()->activo);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.productos.destacado', $producto))
            ->assertRedirect();

        $this->assertTrue($producto->fresh()->destacado);
    }

    public function test_admin_can_upload_an_image_without_extension(): void
    {
        $admin = $this->crearAdmin();

        $categoria = Categoria::create(['nombre' => 'Computadoras', 'activo' => true]);
        $subcategoria = Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'LAPTOPS',
            'activo' => true,
        ]);

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQDJ/pLvAAAAAElFTkSuQmCC');
        $rutaTemporal = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($rutaTemporal, $png);

        $imagen = new UploadedFile($rutaTemporal, 'producto', 'image/png', null, true);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.productos.store'), [
                'nombre' => 'Producto con imagen',
                'sku' => 'IMG-001',
                'id_subcategoria' => $subcategoria->id_subcategoria,
                'marca' => 'Lenovo',
                'precio_compra' => 10,
                'precio_venta' => 20,
                'stock_actual' => 1,
                'activo' => '1',
                'imagen' => $imagen,
            ])
            ->assertRedirect(route('admin.productos.index'));

        $producto = Producto::where('sku', 'IMG-001')->firstOrFail();

        $this->assertNotNull($producto->imagen_principal);
        $this->assertStringEndsWith('.png', $producto->imagen_principal);
        $this->assertFileExists(public_path('productos/'.$producto->imagen_principal));

        @unlink(public_path('productos/'.$producto->imagen_principal));
    }

    public function test_admin_can_add_gallery_images_to_a_spare_part_and_view_its_tabs(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::create(['nombre' => 'Repuestos', 'activo' => true]);
        $subcategoria = Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'REPUESTOS VARIOS',
            'activo' => true,
        ]);

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQDJ/pLvAAAAAElFTkSuQmCC');
        $archivosTemporales = [];
        $imagenes = [];

        foreach (['principal.png', 'vista-1.png', 'vista-2.png'] as $nombre) {
            $rutaTemporal = tempnam(sys_get_temp_dir(), 'img');
            file_put_contents($rutaTemporal, $png);
            $archivosTemporales[] = $rutaTemporal;
            $imagenes[] = new UploadedFile($rutaTemporal, $nombre, 'image/png', null, true);
        }

        $this->actingAs($admin, 'admin')
            ->post(route('admin.productos.store'), [
                'nombre' => 'Ribbon Mask Epson LX350',
                'sku' => '1574516',
                'codigo_barras' => 'REPEPS1574516',
                'id_subcategoria' => $subcategoria->id_subcategoria,
                'marca' => 'EPSON',
                'precio_compra' => 1,
                'precio_venta' => 2.44,
                'stock_actual' => 5,
                'activo' => '1',
                'es_repuesto' => '1',
                'atributos_nombres' => ['Peso'],
                'atributos_valores' => ['0.05 Kg'],
                'imagen' => $imagenes[0],
                'imagenes' => [$imagenes[1], $imagenes[2]],
            ])
            ->assertRedirect(route('admin.productos.index'));

        $producto = Producto::where('sku', '1574516')->firstOrFail();
        $this->assertSame('REPEPS1574516', $producto->codigo_barras);
        $this->assertCount(2, $producto->imagenes_adicionales);

        $this->get(route('producto.show', $producto->id_producto))
            ->assertOk()
            ->assertSee('Información')
            ->assertSee('Imágenes')
            ->assertSee('Soporte')
            ->assertSee('Centro de Servicios CDA System')
            ->assertSee(route('quienes-somos'), false)
            ->assertSee('REPEPS1574516')
            ->assertSee('0.05 Kg');

        foreach (array_merge([$producto->imagen_principal], $producto->imagenes_adicionales) as $imagen) {
            @unlink(public_path('productos/'.$imagen));
        }

        foreach ($archivosTemporales as $rutaTemporal) {
            @unlink($rutaTemporal);
        }
    }

    public function test_admin_generates_spare_part_code_from_brand_and_sku(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::create(['nombre' => 'Repuestos', 'activo' => true]);
        $subcategoria = Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'REPUESTOS VARIOS',
            'activo' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.productos.store'), [
                'nombre' => 'PERILLA IMPRESORA EPSON LX350 NEGRA',
                'sku' => '1529633',
                'id_subcategoria' => $subcategoria->id_subcategoria,
                'marca' => 'EPSON',
                'precio_compra' => 1,
                'precio_venta' => 2.22,
                'stock_actual' => 3,
                'activo' => '1',
                'es_repuesto' => '1',
                'atributos_nombres' => ['Peso'],
                'atributos_valores' => ['0.05 Kg'],
            ])
            ->assertRedirect(route('admin.productos.index'));

        $producto = Producto::where('sku', '1529633')->firstOrFail();

        $this->assertSame('REPEPS1529633', $producto->codigo_barras);

        $this->get(route('producto.show', $producto->id_producto))
            ->assertOk()
            ->assertSee('REPEPS1529633')
            ->assertSee('REPUESTOS VARIOS')
            ->assertSee('0.05 Kg')
            ->assertSee('1529633');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.productos.edit', $producto))
            ->assertOk()
            ->assertSee('generar-codigo', false)
            ->assertSee('REPEPS1529633');
    }

    public function test_spare_part_code_is_generated_from_normalized_brand(): void
    {
        $this->assertSame('REPEPS1529633', Producto::generarCodigoRepuesto('Epson', '1529633'));
        $this->assertSame('REPHP123', Producto::generarCodigoRepuesto(' H.P. ', '123'));
        $this->assertNull(Producto::generarCodigoRepuesto('EPSON', ''));
    }

    public function test_admin_login_sets_admin_session_cookie(): void
    {
        $this->crearAdmin();

        $this->post('/admin/login', [
            'usuario' => 'admin',
            'password' => 'admin123',
        ])->assertCookie('admin_session');
    }

    public function test_client_login_uses_regular_session_cookie(): void
    {
        Cliente::create([
            'nombre' => 'Cliente Prueba',
            'email' => 'cliente@prueba.com',
            'password' => Hash::make('cliente123'),
            'telefono' => '555123456',
            'activo' => true,
        ]);

        $this->post('/login', [
            'email' => 'cliente@prueba.com',
            'password' => 'cliente123',
        ])->assertCookie('laravel_session');
    }

    public function test_admin_can_export_inventory_report_pdf_and_product_technical_sheet_pdf(): void
    {
        $admin = $this->crearAdmin();

        $categoria = Categoria::create(['nombre' => 'Computadoras', 'activo' => true]);
        $subcategoria = Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'LAPTOPS',
            'activo' => true,
        ]);

        $producto = Producto::create([
            'id_subcategoria' => $subcategoria->id_subcategoria,
            'nombre' => 'Laptop Técnica PDF',
            'sku' => 'LAP-PDF',
            'marca' => 'HP',
            'precio_compra' => 300,
            'precio_venta' => 499,
            'stock_actual' => 8,
            'stock_minimo' => 2,
            'condicion' => 'Nuevo',
            'garantia_meses' => 12,
            'activo' => true,
            'descripcion_corta' => 'Laptop para oficina',
            'descripcion_larga' => 'Laptop con 16 GB RAM y SSD de 512 GB.',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.reportes.pdf', ['tipo' => 'inventario']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.productos.pdf', $producto))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
