<?php

namespace Tests\Feature\Services\Preproyectos;

use App\Models\ArchivoProyecto;
use App\Models\Categoria;
use App\Models\PreProyecto;
use App\Models\Producto;
use App\Models\User;
use App\Services\Preproyectos\PreProjectCreator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PreProjectCreatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_persists_a_pre_project_with_expected_payload(): void
    {
        $creator = app(PreProjectCreator::class);
        $user = User::factory()->create();
        $categoria = Categoria::factory()->create(['nombre' => 'Uniformes']);
        $producto = Producto::create([
            'categoria_id' => $categoria->id,
            'nombre' => 'Polo',
            'dias_produccion' => 4,
            'flag_armado' => 0,
            'flag_requiere_proveedor' => 1,
            'ind_activo' => 1,
        ]);

        $preProyecto = $creator->create([
            'selected_user_id' => $user->id,
            'nombre' => 'Preproyecto QA',
            'descripcion' => 'Creado desde prueba',
            'fecha_produccion' => '2026-04-01',
            'fecha_embarque' => '2026-04-03',
            'fecha_entrega' => '2026-04-07',
            'seleccion_armado' => 1,
            'flag_requiere_proveedor' => 1,
            'categoria_id' => $categoria->id,
            'producto_id' => $producto->id,
            'caracteristicas_sel' => [['id' => 1, 'nombre' => 'Color', 'opciones' => [['id' => 9, 'nombre' => 'Negro', 'valoru' => 4]]]],
            'opciones_sel' => [1 => ['id' => 9, 'nombre' => 'Negro', 'valoru' => 4]],
            'direccion_entrega_id' => null,
            'direccion_fiscal_id' => null,
            'id_tipo_envio' => 1,
            'total_piezas' => 24,
            'detalle_tallas' => ['1' => ['10' => 12, '11' => 12]],
        ]);

        $this->assertInstanceOf(PreProyecto::class, $preProyecto);
        $this->assertDatabaseHas('pre_proyectos', [
            'id' => $preProyecto->id,
            'usuario_id' => $user->id,
            'nombre' => 'Preproyecto QA',
        ]);

        $rawRow = (array) \DB::table('pre_proyectos')->where('id', $preProyecto->id)->first();
        $this->assertStringContainsString((string) $categoria->id, $rawRow['categoria_sel']);
        $this->assertStringContainsString((string) $producto->id, $rawRow['producto_sel']);
        $this->assertStringContainsString('24', $rawRow['total_piezas_sel']);
    }

    public function test_store_uploaded_files_creates_attachment_records(): void
    {
        Storage::fake('public');

        $creator = app(PreProjectCreator::class);
        $user = User::factory()->create();
        $preProyecto = PreProyecto::create([
            'usuario_id' => $user->id,
            'nombre' => 'Temporal',
            'descripcion' => 'Temporal',
            'id_tipo_envio' => 1,
            'tipo' => 'PROYECTO',
            'estado' => 'PENDIENTE',
        ]);
        $file = UploadedFile::fake()->image('referencia.jpg');

        $this->actingAs($user);
        $creator->storeUploadedFiles($preProyecto, [$file], ['Vista frontal']);

        $this->assertDatabaseCount('archivos_proyecto', 1);

        $archivo = ArchivoProyecto::first();
        $this->assertSame($preProyecto->id, $archivo->pre_proyecto_id);
        $this->assertSame('Vista frontal', $archivo->descripcion);
        Storage::disk('public')->assertExists($archivo->ruta_archivo);
    }
}

