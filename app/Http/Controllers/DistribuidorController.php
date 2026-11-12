<?php

namespace App\Http\Controllers;

use App\Models\SolicitudDistribuidor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DistribuidorController extends Controller
{
    private const PROVINCIAS = [
        'Azuay', 'Bolívar', 'Cañar', 'Carchi', 'Chimborazo', 'Cotopaxi', 'El Oro',
        'Esmeraldas', 'Galápagos', 'Guayas', 'Imbabura', 'Loja', 'Los Ríos',
        'Manabí', 'Morona Santiago', 'Napo', 'Orellana', 'Pastaza', 'Pichincha',
        'Santa Elena', 'Santo Domingo de los Tsáchilas', 'Sucumbíos',
        'Tungurahua', 'Zamora Chinchipe',
    ];

    private const OFICINAS = [
        'Quito', 'Guayaquil', 'Cuenca', 'Ambato', 'Manta', 'Machala',
    ];

    public function create()
    {
        return view('tienda.ser-distribuidor', [
            'provincias' => self::PROVINCIAS,
            'oficinas' => self::OFICINAS,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'email_contacto' => mb_strtolower(trim((string) $request->input('email_contacto'))),
        ]);

        $datos = $request->validate([
            'ruc' => ['required', 'string', 'digits:13'],
            'representante_legal' => ['required', 'string', 'max:150'],
            'actividad_tecnologia' => ['required', 'boolean'],
            'tiempo_negocio' => ['required', 'string', 'max:100'],
            'provincia' => ['required', 'string', 'max:100', Rule::in(self::PROVINCIAS)],
            'canton' => ['required', 'string', 'min:2', 'max:100'],
            'parroquia' => ['required', 'string', 'min:2', 'max:100'],
            'calle_principal' => ['required', 'string', 'min:3', 'max:150'],
            'numero_predio' => ['required', 'string', 'min:1', 'max:30'],
            'calle_secundaria' => ['nullable', 'string', 'max:150'],
            'referencia_adicional' => ['nullable', 'string', 'max:255'],
            'telefono_local' => ['required', 'string', 'regex:/^(?:\+?\d{1,3})?[\s\-]?(?:\(?\d{2,4}\)?[\s\-]?){2,4}\d{2,4}$/', 'max:20'],
            'celular' => ['required', 'string', 'regex:/^(?:\+?\d{1,3})?[\s\-]?(?:\(?\d{2,4}\)?[\s\-]?){2,4}\d{2,4}$/', 'max:20'],
            'email_contacto' => ['required', 'string', 'email', 'max:150'],
            'oficina' => ['required', 'string', 'max:100', Rule::in(self::OFICINAS)],
            'acepta_terminos' => ['accepted'],
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'email' => 'El campo :attribute debe ser un correo electrónico válido.',
            'accepted' => 'Debes aceptar los términos y condiciones.',
            'boolean' => 'El campo :attribute debe ser Sí o No.',
            'digits' => 'El campo :attribute debe contener exactamente :digits dígitos.',
            'max' => 'El campo :attribute no debe superar :max caracteres.',
            'min' => 'El campo :attribute debe tener al menos :min caracteres.',
            'in' => 'El valor seleccionado en :attribute no es válido.',
            'regex' => 'El campo :attribute tiene un formato inválido.',
        ], [
            'ruc' => 'RUC',
            'representante_legal' => 'representante legal',
            'actividad_tecnologia' => 'actividad relacionada con la compra y/o venta de tecnología',
            'tiempo_negocio' => 'tiempo en el negocio',
            'provincia' => 'provincia',
            'canton' => 'cantón',
            'parroquia' => 'parroquia',
            'calle_principal' => 'calle principal',
            'numero_predio' => 'número de predio / casa',
            'calle_secundaria' => 'calle secundaria',
            'referencia_adicional' => 'referencia adicional',
            'telefono_local' => 'teléfono local',
            'celular' => 'celular',
            'email_contacto' => 'email de contacto',
            'oficina' => 'oficina',
            'acepta_terminos' => 'términos y condiciones',
        ]);

        $datos['actividad_tecnologia'] = $request->boolean('actividad_tecnologia');
        $datos['acepta_terminos'] = true;
        $datos['estado'] = 'Pendiente';
        $datos['fecha_solicitud'] = now();

        SolicitudDistribuidor::create($datos);

        return redirect()
            ->route('distribuidor.create')
            ->with('success', 'Tu solicitud de distribuidor fue enviada correctamente. Nos pondremos en contacto contigo.');
    }
}
