    <footer id="contacto" class="bg-gray-900 text-gray-300 mt-12">
        <div class="max-w-7xl mx-auto px-4 py-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-10 h-10 bg-blue-600 text-white flex items-center justify-center font-bold text-xl rounded">T</div>
                    <div>
                        <p class="text-lg font-bold text-white leading-tight">CDA System</p>
                        <p class="text-xs text-gray-400 italic">Store</p>
                    </div>
                </div>
                <p class="text-sm leading-relaxed">Distribución de tecnología con calidad, garantía y soporte para clientes y aliados comerciales.</p>
            </div>

            <div>
                <h3 class="text-white font-bold mb-4">Sitios</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/') }}" class="hover:text-yellow-500">Tienda online</a></li>
                    <li><a href="{{ route('marcas') }}" class="hover:text-yellow-500">Marcas</a></li>
                    <li><a href="{{ route('quienes-somos.repuestos') }}" class="hover:text-yellow-500">Repuestos</a></li>
                    <li><a href="{{ route('distribuidor.create') }}" class="hover:text-yellow-500">Ser Distribuidor</a></li>
                    <li><a href="{{ route('centro-servicio') }}" class="hover:text-yellow-500">Centro de Servicio</a></li>
                    <li><a href="{{ route('quienes-somos') }}#visitanos" class="hover:text-yellow-500">Quasad Computer</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-white font-bold mb-4">Legal</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('quienes-somos') }}#contacto" class="hover:text-yellow-500">Políticas de garantía</a></li>
                    <li><a href="{{ route('quienes-somos') }}#contacto" class="hover:text-yellow-500">Políticas de Protección de Datos</a></li>
                    <li><a href="{{ route('quienes-somos') }}#contacto" class="hover:text-yellow-500">Contáctanos</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-white font-bold mb-4">Contáctanos</h3>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-location-dot mt-1"></i>
                        <span>Ubicaciones en todas las provincias del Ecuador</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-phone"></i>
                        <a href="tel:+593980821599" class="hover:text-yellow-500">098 082 1599</a>
                    </li>
                    <li class="pt-2">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-3">Síguenos</p>
                        <div class="flex items-center gap-3">
                            <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-700 text-gray-300 hover:border-blue-500 hover:text-blue-400 transition" aria-label="Facebook">
                                <i class="fa-brands fa-facebook-f"></i>
                            </a>
                            <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-700 text-gray-300 hover:border-pink-500 hover:text-pink-400 transition" aria-label="Instagram">
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                            <a href="https://wa.me/593980821599" target="_blank" rel="noopener noreferrer" class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-700 text-gray-300 hover:border-green-500 hover:text-green-400 transition" aria-label="WhatsApp">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>
                            <a href="https://www.tiktok.com" target="_blank" rel="noopener noreferrer" class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-700 text-gray-300 hover:border-gray-100 hover:text-white transition" aria-label="TikTok">
                                <i class="fa-brands fa-tiktok"></i>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 py-4 text-center text-xs text-gray-500">
                Copyright © {{ date('Y') }}. CDA System C.A. Todos los derechos reservados.
            </div>
        </div>
    </footer>
