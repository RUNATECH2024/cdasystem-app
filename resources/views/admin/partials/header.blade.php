@php
    $adminNavigation = [
        ['route' => 'admin.dashboard', 'label' => 'Inicio', 'active' => 'admin.dashboard', 'icon' => 'home'],
        ['route' => 'admin.productos.index', 'label' => 'Productos', 'active' => 'admin.productos.*', 'icon' => 'box'],
        ['route' => 'admin.proveedores.index', 'label' => 'Proveedores', 'active' => 'admin.proveedores.*', 'icon' => 'truck'],
        ['route' => 'admin.compras.index', 'label' => 'Compras', 'active' => 'admin.compras.*', 'icon' => 'receipt'],
        ['route' => 'admin.pedidos.index', 'label' => 'Pedidos web', 'active' => 'admin.pedidos.*', 'icon' => 'cart'],
        ['route' => 'admin.clientes.index', 'label' => 'Clientes', 'active' => 'admin.clientes.*', 'icon' => 'users'],
        ['route' => 'admin.ordenes.index', 'label' => 'Órdenes de taller', 'active' => 'admin.ordenes.*', 'icon' => 'wrench'],
        ['route' => 'admin.reportes.index', 'label' => 'Reportes', 'active' => 'admin.reportes.*', 'icon' => 'chart'],
        ['route' => 'admin.usuarios.index', 'label' => 'Usuarios', 'active' => 'admin.usuarios.*', 'icon' => 'user'],
        ['route' => 'admin.auditoria.index', 'label' => 'Auditoría', 'active' => 'admin.auditoria.*', 'icon' => 'shield'],
        ['route' => 'admin.respaldos.index', 'label' => 'Respaldos', 'active' => 'admin.respaldos.*', 'icon' => 'database'],
    ];
@endphp

<header class="sticky top-0 z-50 bg-slate-900 text-slate-100 shadow-lg">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="flex h-16 items-center justify-between gap-4">
            <a href="{{ route('admin.dashboard') }}" class="group flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 text-[11px] font-black tracking-wider text-white shadow-lg shadow-blue-900/30">CDA</span>
                <span class="min-w-0">
                    <span class="block truncate font-bold leading-tight tracking-tight group-hover:text-white">CDA System</span>
                    <span class="block text-[10px] uppercase tracking-[0.2em] text-slate-400">Panel administrativo</span>
                </span>
            </a>

            <div class="flex items-center gap-2">
                <a href="{{ url('/') }}" class="hidden items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-medium text-slate-300 transition hover:bg-white/5 hover:text-white sm:inline-flex">
                    <x-icon name="cart" /> Ver tienda
                </a>

                @auth('admin')
                    <div class="hidden items-center gap-3 border-l border-white/10 pl-3 md:flex">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">
                            {{ mb_strtoupper(mb_substr(Auth::guard('admin')->user()->nombre, 0, 1)) }}
                        </span>
                        <div class="max-w-40 leading-tight">
                            <p class="truncate text-xs font-semibold">{{ Auth::guard('admin')->user()->nombre }}</p>
                            <p class="text-[10px] uppercase tracking-wide text-slate-400">{{ Auth::guard('admin')->user()->rol }}</p>
                        </div>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" title="Cerrar sesión" class="inline-flex h-9 items-center gap-1.5 rounded-lg px-2.5 text-xs font-medium text-slate-300 transition hover:bg-red-500/10 hover:text-red-300">
                                <x-icon name="logout" /> <span class="hidden xl:inline">Salir</span>
                            </button>
                        </form>
                    </div>
                @endauth

                <button id="admin-menu-toggle" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-300 transition hover:bg-white/10 hover:text-white md:hidden" aria-label="Abrir menú" aria-expanded="false" aria-controls="admin-mobile-nav">
                    <x-icon id="admin-menu-open" name="menu" class="h-5 w-5" />
                    <x-icon id="admin-menu-close" name="close" class="hidden h-5 w-5" />
                </button>
            </div>
        </div>

        <nav class="hidden items-center gap-0.5 overflow-x-auto border-t border-white/10 py-1 md:flex" aria-label="Navegación principal">
            @foreach($adminNavigation as $item)
                @php($isActive = request()->routeIs($item['active']))
                <a href="{{ route($item['route']) }}" class="group inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-lg px-3 py-2 text-xs font-medium transition {{ $isActive ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}" @if($isActive) aria-current="page" @endif>
                    <x-icon :name="$item['icon']" class="h-4 w-4 {{ $isActive ? 'text-blue-400' : 'text-slate-400 group-hover:text-blue-300' }}" />
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    <nav id="admin-mobile-nav" class="hidden border-t border-white/10 bg-slate-900 px-4 pb-4 pt-2 md:hidden" aria-label="Navegación móvil">
        <div class="grid grid-cols-2 gap-1">
            @foreach($adminNavigation as $item)
                @php($isActive = request()->routeIs($item['active']))
                <a href="{{ route($item['route']) }}" class="inline-flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}" @if($isActive) aria-current="page" @endif>
                    <x-icon :name="$item['icon']" class="h-4 w-4 {{ $isActive ? 'text-blue-400' : 'text-slate-400' }}" />
                    {{ $item['label'] }}
                </a>
            @endforeach
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white">
                <x-icon name="cart" class="h-4 w-4 text-slate-400" /> Ver tienda
            </a>
        </div>

        @auth('admin')
            <div class="mt-3 flex items-center justify-between gap-3 border-t border-white/10 pt-3">
                <div class="flex min-w-0 items-center gap-2">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">
                        {{ mb_strtoupper(mb_substr(Auth::guard('admin')->user()->nombre, 0, 1)) }}
                    </span>
                    <div class="min-w-0 leading-tight">
                        <p class="truncate text-xs font-semibold">{{ Auth::guard('admin')->user()->nombre }}</p>
                        <p class="text-[10px] uppercase tracking-wide text-slate-400">{{ Auth::guard('admin')->user()->rol }}</p>
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-2 text-xs font-medium text-slate-300 transition hover:bg-red-500/10 hover:text-red-300">
                        <x-icon name="logout" /> Cerrar sesión
                    </button>
                </form>
            </div>
        @endauth
    </nav>
</header>

<script>
    (() => {
        const toggle = document.getElementById('admin-menu-toggle');
        const menu = document.getElementById('admin-mobile-nav');
        const openIcon = document.getElementById('admin-menu-open');
        const closeIcon = document.getElementById('admin-menu-close');

        if (!toggle || !menu) return;

        const setOpen = (open) => {
            menu.classList.toggle('hidden', !open);
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
            openIcon?.classList.toggle('hidden', open);
            closeIcon?.classList.toggle('hidden', !open);
        };

        toggle.addEventListener('click', () => setOpen(menu.classList.contains('hidden')));
        menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setOpen(false)));
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) setOpen(false);
        });
    })();
</script>
