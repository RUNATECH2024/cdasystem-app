<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Subcategoria;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $categorias = Categoria::where('activo', true)
            ->orderBy('orden_visual')
            ->get();

        $subcategorias = Subcategoria::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $productos = Producto::where('activo', true)
            ->orderByDesc('id_producto')
            ->get();

        return response()
            ->view('sitemap', compact('categorias', 'subcategorias', 'productos'))
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $contenido = "User-agent: *\nAllow: /\nDisallow: /admin\n\nSitemap: ".url('/sitemap.xml')."\n";

        return response($contenido, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
