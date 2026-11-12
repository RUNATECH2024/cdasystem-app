    <!-- Barra Superior -->
    <div class="bg-gray-800 text-gray-200 text-xs">
        <div class="max-w-7xl mx-auto px-4 py-2 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="ml-auto flex w-full max-w-full flex-nowrap items-center justify-end gap-4 overflow-x-auto whitespace-nowrap">
                <a href="{{ route('distribuidor.create') }}" class="hover:text-yellow-500">¿Quieres ser distribuidor?</a>
                <a href="{{ route('quienes-somos') }}" class="hover:text-yellow-500 {{ request()->routeIs('quienes-somos') ? 'text-yellow-400' : '' }}">¿Quiénes somos?</a>
                <div class="flex items-center gap-3 text-sm" aria-label="Redes sociales">
                    <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" class="hover:text-blue-400" aria-label="Facebook">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" class="hover:text-pink-400" aria-label="Instagram">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="https://wa.me/593980821599" target="_blank" rel="noopener noreferrer" class="hover:text-green-400" aria-label="WhatsApp">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                    <a href="https://www.tiktok.com" target="_blank" rel="noopener noreferrer" class="hover:text-white" aria-label="TikTok">
                        <i class="fa-brands fa-tiktok"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Header (Logo, Buscador, Carrito) -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col lg:flex-row lg:items-center gap-4">
            <a href="/" class="flex items-center space-x-2 shrink-0">
                <div class="w-10 h-10 bg-blue-600 text-white flex items-center justify-center font-bold text-xl rounded">T</div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800 leading-tight">CDA System</h1>
                    <p class="text-xs text-gray-500 italic">Store</p>
                </div>
            </a>

            <!-- Buscador -->
            <form action="{{ route('buscar') }}" method="GET" class="flex flex-1 lg:max-w-2xl">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="¿Qué estás buscando?" class="w-full border border-gray-300 rounded-l px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded-r hover:bg-yellow-600" aria-label="Buscar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>
            </form>

            <!-- Iconos -->
            <div class="flex items-center space-x-6 text-gray-700 lg:ml-auto">
                @include('tienda.partials.auth-links')

                <a href="{{ route('carrito.index') }}" class="relative hover:text-yellow-500" title="Carrito">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="absolute -top-2 -right-2 bg-yellow-500 text-white text-xs rounded-full px-1.5">{{ count(session()->get('carrito', [])) }}</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Navegación principal -->
    <nav class="bg-gray-700 text-white">
        <div class="max-w-7xl mx-auto px-4 flex items-center gap-6 py-2 text-sm font-medium overflow-x-auto">
            <a href="{{ url('/') }}" class="whitespace-nowrap hover:text-yellow-500 flex items-center space-x-2 {{ request()->is('/') ? 'text-yellow-400' : '' }}">
                <i class="fa-solid fa-house"></i>
                <span>Tienda online</span>
            </a>
            <a href="{{ url('/') }}#categorias" class="whitespace-nowrap hover:text-yellow-500 flex items-center space-x-2">
                <i class="fa-solid fa-layer-group"></i>
                <span>Categorías</span>
            </a>
            <a href="{{ route('marcas') }}" class="whitespace-nowrap hover:text-yellow-500 flex items-center space-x-2 {{ request()->routeIs('marcas') ? 'text-yellow-400' : '' }}">
                <i class="fa-solid fa-warehouse"></i>
                <span>Marcas</span>
            </a>
            <a href="{{ route('centro-servicio') }}" class="whitespace-nowrap hover:text-yellow-500 flex items-center space-x-2 {{ request()->routeIs('centro-servicio') ? 'text-yellow-400' : '' }}">
                <i class="fa-solid fa-screwdriver-wrench"></i>
                <span>Centro de Servicio</span>
            </a>
        </div>
    </nav>
