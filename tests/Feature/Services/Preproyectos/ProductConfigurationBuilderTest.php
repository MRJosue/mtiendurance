<?php

namespace Tests\Feature\Services\Preproyectos;

use App\Models\Caracteristica;
use App\Models\Categoria;
use App\Models\GrupoTalla;
use App\Models\Opcion;
use App\Models\Producto;
use App\Models\Talla;
use App\Services\Preproyectos\ProductConfigurationBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductConfigurationBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_returns_sizes_and_auto_selected_single_options(): void
    {
        $builder = app(ProductConfigurationBuilder::class);
        $categoria = Categoria::factory()->create();
        $producto = Producto::create([
            'categoria_id' => $categoria->id,
            'nombre' => 'Sudadera',
            'dias_produccion' => 5,
            'flag_armado' => 1,
            'flag_requiere_proveedor' => 0,
            'ind_activo' => 1,
        ]);

        $grupoTalla = GrupoTalla::create([
            'nombre' => 'Adulto',
            'ind_activo' => 1,
        ]);
        $talla = Talla::create([
            'nombre' => 'M',
            'descripcion' => 'Mediana',
            'ind_activo' => 1,
        ]);
        $grupoTalla->tallas()->attach($talla->id);
        $producto->gruposTallas()->attach($grupoTalla->id);

        $caracteristicaUnica = Caracteristica::create([
            'nombre' => 'Color',
            'flag_seleccion_multiple' => 0,
            'ind_activo' => 1,
        ]);
        $caracteristicaMultiple = Caracteristica::create([
            'nombre' => 'Acabado',
            'flag_seleccion_multiple' => 1,
            'ind_activo' => 1,
        ]);

        $producto->caracteristicas()->attach($caracteristicaUnica->id, ['flag_armado' => 1]);
        $producto->caracteristicas()->attach($caracteristicaMultiple->id, ['flag_armado' => 1]);

        $opcionUnica = Opcion::create([
            'nombre' => 'Rojo',
            'pasos' => 1,
            'minutoPaso' => 1,
            'valoru' => 10,
            'ind_activo' => 1,
        ]);
        $opcionA = Opcion::create([
            'nombre' => 'Mate',
            'pasos' => 1,
            'minutoPaso' => 1,
            'valoru' => 5,
            'ind_activo' => 1,
        ]);
        $opcionB = Opcion::create([
            'nombre' => 'Brillante',
            'pasos' => 1,
            'minutoPaso' => 1,
            'valoru' => 7,
            'ind_activo' => 1,
        ]);

        $caracteristicaUnica->opciones()->attach($opcionUnica->id);
        $caracteristicaMultiple->opciones()->attach([$opcionA->id, $opcionB->id]);

        $configuration = $builder->build($producto->id, true, 1);

        $this->assertCount(1, $configuration['tallas']);
        $this->assertSame(0, $configuration['tallasSeleccionadas'][$grupoTalla->id][$talla->id]);
        $this->assertCount(2, $configuration['caracteristicas_sel']);
        $this->assertCount(1, $configuration['caracteristicas_sel'][0]['opciones']);
        $this->assertSame('Rojo', $configuration['opciones_sel'][$caracteristicaUnica->id]['nombre']);
        $this->assertCount(2, $configuration['caracteristicaOpcionesDisponibles'][$caracteristicaMultiple->id]);
    }
}
