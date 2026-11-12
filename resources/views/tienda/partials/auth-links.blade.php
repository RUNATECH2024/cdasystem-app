@auth('cliente')
    <span class="text-sm text-gray-600">Hola, {{ auth('cliente')->user()->nombre }}</span>

    <form action="{{ route('logout') }}" method="POST" class="inline">
        @csrf
        <button type="submit" class="flex items-center space-x-1 hover:text-yellow-500" title="Cerrar sesión">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            <span>Cerrar sesión</span>
        </button>
    </form>
@else
    <a href="{{ route('login') }}" class="flex items-center space-x-1 hover:text-yellow-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        <span>Iniciar sesión</span>
    </a>

    <a href="{{ route('register') }}" class="hover:text-yellow-500">Registrarse</a>
@endauth
