<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            ['nombre' => 'Administrador', 'usuario' => 'admin', 'rol' => 'Admin'],
            ['nombre' => 'Carlos Vendedor', 'usuario' => 'carlos', 'rol' => 'Vendedor'],
            ['nombre' => 'Pedro Técnico', 'usuario' => 'pedro', 'rol' => 'Tecnico'],
        ];

        $password = env('SEED_USER_PASSWORD', 'admin123');

        foreach ($usuarios as $datos) {
            Usuario::firstOrCreate(
                ['usuario' => $datos['usuario']],
                [
                    'nombre' => $datos['nombre'],
                    'password_hash' => Hash::make($password),
                    'rol' => $datos['rol'],
                    'activo' => true,
                ]
            );
        }
    }
}
