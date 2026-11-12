<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSeguridadTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(string $rol = 'Admin'): Usuario
    {
        return Usuario::create([
            'nombre' => 'Admin de prueba',
            'usuario' => 'admin',
            'password_hash' => Hash::make('contrasena-segura-123'),
            'rol' => $rol,
            'activo' => true,
        ]);
    }

    public function test_admin_can_create_staff_user_and_creation_is_audited_without_password_values(): void
    {
        $admin = $this->crearUsuario();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.usuarios.store'), [
                'nombre' => 'Técnico Nuevo',
                'usuario' => 'tecnico_nuevo',
                'rol' => 'Tecnico',
                'password' => 'una-clave-muy-segura',
                'password_confirmation' => 'una-clave-muy-segura',
            ])
            ->assertRedirect(route('admin.usuarios.index'));

        $this->assertDatabaseHas('usuarios', [
            'nombre' => 'Técnico Nuevo',
            'usuario' => 'tecnico_nuevo',
            'rol' => 'Tecnico',
            'activo' => true,
        ]);

        $creado = Usuario::where('usuario', 'tecnico_nuevo')->firstOrFail();
        $this->assertTrue(Hash::check('una-clave-muy-segura', $creado->password_hash));

        $this->get(route('admin.usuarios.index'))
            ->assertOk()
            ->assertSee('Técnico Nuevo');

        $evento = AdminAuditLog::where('accion', 'admin.usuarios.store')->firstOrFail();
        $this->assertSame('admin', $evento->actor_usuario);
        $this->assertContains('rol', $evento->metadata['campos_enviados']);
        $this->assertNotContains('password', $evento->metadata['campos_enviados']);
        $this->assertStringNotContainsString('una-clave-muy-segura', json_encode($evento->metadata));
    }

    public function test_non_admin_cannot_access_user_audit_or_backup_pages(): void
    {
        $vendedor = $this->crearUsuario('Vendedor');

        $this->actingAs($vendedor, 'admin')
            ->get(route('admin.usuarios.index'))
            ->assertForbidden();

        $this->get(route('admin.auditoria.index'))->assertForbidden();
        $this->get(route('admin.respaldos.index'))->assertForbidden();
    }

    public function test_admin_can_review_audit_events_and_download_private_sql_backup(): void
    {
        $admin = $this->crearUsuario();
        AdminAuditLog::create([
            'id_usuario' => $admin->id_usuario,
            'actor_usuario' => $admin->usuario,
            'actor_rol' => $admin->rol,
            'accion' => 'admin.usuarios.store',
            'metodo_http' => 'POST',
            'ruta' => 'admin/usuarios',
            'metadata' => ['campos_enviados' => ['nombre', 'rol']],
            'codigo_respuesta' => 302,
            'created_at' => now(),
        ]);
        Storage::fake('local');
        Storage::disk('local')->put('backups/cda_backup_test.sql', 'SELECT 1;');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.auditoria.index'))
            ->assertOk()
            ->assertSee('admin.usuarios.store')
            ->assertSee('nombre, rol');

        $this->get(route('admin.respaldos.index'))
            ->assertOk()
            ->assertSee('cda_backup_test.sql');

        $this->get(route('admin.respaldos.download', 'cda_backup_test.sql'))
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->assertDatabaseHas('admin_audit_logs', [
            'accion' => 'admin.respaldos.download',
            'actor_usuario' => 'admin',
        ]);
    }
}
