<?php

use App\Models\Cliente;
use App\Models\User;
use App\Models\Usuario;

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // GUARD PARA LOS CLIENTES DE LA TIENDA
        'cliente' => [
            'driver' => 'session',
            'provider' => 'clientes',
        ],

        // GUARD PARA EL PERSONAL / ADMINISTRACIÓN (tabla usuarios)
        'admin' => [
            'driver' => 'session',
            'provider' => 'usuarios',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

        // PROVIDER PARA LOS CLIENTES
        'clientes' => [
            'driver' => 'eloquent',
            'model' => Cliente::class,
        ],

        // PROVIDER PARA EL PERSONAL / ADMINISTRACIÓN
        'usuarios' => [
            'driver' => 'eloquent',
            'model' => Usuario::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
