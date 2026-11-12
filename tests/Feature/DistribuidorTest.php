<?php

namespace Tests\Feature;

use App\Models\SolicitudDistribuidor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistribuidorTest extends TestCase
{
    use RefreshDatabase;

    private function datosValidos(array $extra = []): array
    {
        return array_merge([
            'ruc' => '1790012345001',
            'representante_legal' => 'Ana Pérez',
            'actividad_tecnologia' => '1',
            'tiempo_negocio' => '5 años',
            'provincia' => 'Pichincha',
            'canton' => 'Quito',
            'parroquia' => 'Centro Histórico',
            'calle_principal' => 'Av. Amazonas',
            'numero_predio' => '123',
            'calle_secundaria' => 'Av. Naciones Unidas',
            'referencia_adicional' => 'Frente al parque',
            'telefono_local' => '022222218',
            'celular' => '0999999999',
            'email_contacto' => 'DISTRIBUIDOR@EXAMPLE.COM',
            'oficina' => 'Quito',
            'acepta_terminos' => '1',
        ], $extra);
    }

    public function test_distributor_form_is_available(): void
    {
        $this->get(route('distribuidor.create'))
            ->assertOk()
            ->assertSee('Formulario de solicitud de distribuidor')
            ->assertSee('RUC')
            ->assertSee('Representante legal')
            ->assertSee('Aceptar')
            ->assertSee('Enviar información');
    }

    public function test_home_links_to_distributor_form(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('distribuidor.create'));
    }

    public function test_guest_can_submit_distributor_request(): void
    {
        $this->post(route('distribuidor.store'), $this->datosValidos())
            ->assertRedirect(route('distribuidor.create'))
            ->assertSessionHas('success');

        $solicitud = SolicitudDistribuidor::firstOrFail();

        $this->assertSame('1790012345001', $solicitud->ruc);
        $this->assertSame('Ana Pérez', $solicitud->representante_legal);
        $this->assertTrue($solicitud->actividad_tecnologia);
        $this->assertTrue($solicitud->acepta_terminos);
        $this->assertSame('distribuidor@example.com', $solicitud->email_contacto);
        $this->assertSame('Pendiente', $solicitud->estado);

        $this->assertDatabaseHas('solicitudes_distribuidor', [
            'ruc' => '1790012345001',
            'email_contacto' => 'distribuidor@example.com',
            'oficina' => 'Quito',
        ]);
    }

    public function test_distributor_request_requires_all_mandatory_fields(): void
    {
        $this->from(route('distribuidor.create'))
            ->post(route('distribuidor.store'), [])
            ->assertRedirect(route('distribuidor.create'))
            ->assertSessionHasErrors([
                'ruc', 'representante_legal', 'actividad_tecnologia', 'tiempo_negocio',
                'provincia', 'canton', 'parroquia', 'calle_principal', 'numero_predio',
                'telefono_local', 'celular', 'email_contacto', 'oficina', 'acepta_terminos',
            ]);

        $this->assertDatabaseCount('solicitudes_distribuidor', 0);
    }

    public function test_distributor_request_rejects_invalid_province_office_and_terms(): void
    {
        $this->post(route('distribuidor.store'), $this->datosValidos([
            'provincia' => 'Provincia inventada',
            'oficina' => 'Oficina inventada',
            'acepta_terminos' => null,
        ]))
            ->assertSessionHasErrors(['provincia', 'oficina', 'acepta_terminos']);

        $this->assertDatabaseCount('solicitudes_distribuidor', 0);
    }
}
