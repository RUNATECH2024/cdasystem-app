<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Support\PasswordVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ClienteAuthController extends Controller
{
    // Mostrar formulario de login
    public function showLogin()
    {
        return view('tienda.login');
    }

    // Procesar el login
    public function login(Request $request)
    {
        $datos = $request->validate([
            'email' => ['required', 'string', 'email', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $email = mb_strtolower(trim($datos['email']));
        $cliente = Cliente::whereRaw('LOWER(email) = ?', [$email])->first();

        $passwordValida = PasswordVerifier::check($cliente?->password, $datos['password']);

        if (! $cliente || ! $cliente->activo || ! $passwordValida) {
            return back()
                ->withErrors(['email' => 'Las credenciales no coinciden con nuestros registros.'])
                ->onlyInput('email');
        }

        Auth::guard('cliente')->login($cliente);
        $request->session()->regenerate();

        return redirect('/')->with('success', '¡Bienvenido de vuelta, '.$cliente->nombre.'!');
    }

    // Mostrar formulario de registro
    public function showRegister()
    {
        return view('tienda.register');
    }

    // Procesar el registro
    public function register(Request $request)
    {
        $email = mb_strtolower(trim((string) $request->input('email')));
        $request->merge(['email' => $email]);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'email' => [
                'required', 'string', 'email', 'max:100',
                Rule::unique('clientes', 'email')
                    ->where(fn ($query) => $query->whereRaw('LOWER(email) = ?', [$email])),
            ],
            'password' => ['required', 'string', 'max:255', 'confirmed', Password::min(8)->letters()->numbers()],
            'telefono' => ['required', 'string', 'max:20'],
        ]);

        $cliente = Cliente::create([
            'nombre' => $datos['nombre'],
            'email' => $datos['email'],
            'password' => Hash::make($datos['password']),
            'telefono' => $datos['telefono'],
            'activo' => true,
        ]);

        Auth::guard('cliente')->login($cliente);
        $request->session()->regenerate();

        return redirect('/')->with('success', '¡Cuenta creada con éxito! Ya puedes comprar.');
    }

    // Cerrar sesión
    public function logout(Request $request)
    {
        Auth::guard('cliente')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Has cerrado sesión.');
    }
}
