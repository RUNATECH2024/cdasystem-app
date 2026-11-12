<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Subcategoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_page_lists_its_active_subcategories(): void
    {
        $categoria = Categoria::create(['nombre' => 'Computadoras', 'activo' => true]);

        Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'LAPTOPS',
            'activo' => true,
        ]);

        Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'MINI PC',
            'activo' => true,
        ]);

        Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'OCULTA',
            'activo' => false,
        ]);

        $this->get(route('categoria.show', $categoria->id_categoria))
            ->assertOk()
            ->assertSee('LAPTOPS')
            ->assertSee('MINI PC')
            ->assertSee('Categorias/computadores.webp')
            ->assertDontSee('OCULTA');
    }

    public function test_home_links_categories_to_their_page(): void
    {
        $categoria = Categoria::create(['nombre' => 'Computadoras', 'activo' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee(route('categoria.show', $categoria->id_categoria))
            ->assertSee('Categorias/computadores.webp');
    }

    public function test_category_without_image_uses_its_icon(): void
    {
        Categoria::create(['nombre' => 'Categoría Especial', 'icono' => 'fa-star', 'activo' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee('fa-star');
    }

    public function test_home_only_lists_active_categories_and_counts_active_subcategories(): void
    {
        $categoria = Categoria::create(['nombre' => 'Computadoras', 'activo' => true]);

        Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'LAPTOPS',
            'activo' => true,
        ]);

        Subcategoria::create([
            'id_categoria' => $categoria->id_categoria,
            'nombre' => 'OCULTA',
            'activo' => false,
        ]);

        Categoria::create(['nombre' => 'Categoría Oculta', 'activo' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Computadoras')
            ->assertSee('1 subcategoría')
            ->assertDontSee('Categoría Oculta');
    }
}
