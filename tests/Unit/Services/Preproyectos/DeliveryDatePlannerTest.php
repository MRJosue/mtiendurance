<?php

namespace Tests\Unit\Services\Preproyectos;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\TipoEnvio;
use App\Services\Preproyectos\DeliveryDatePlanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryDatePlannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_adjust_to_weekday_moves_weekend_dates_to_monday(): void
    {
        $planner = app(DeliveryDatePlanner::class);

        $this->assertSame('2026-03-30', $planner->adjustToWeekday('2026-03-28'));
        $this->assertSame('2026-03-30', $planner->adjustToWeekday('2026-03-29'));
        $this->assertSame('2026-03-30', $planner->adjustToWeekday('2026-03-30'));
    }

    public function test_calculate_uses_product_and_shipping_lead_times(): void
    {
        $planner = app(DeliveryDatePlanner::class);
        $categoria = Categoria::factory()->create();
        $producto = Producto::create([
            'categoria_id' => $categoria->id,
            'nombre' => 'Playera prueba',
            'dias_produccion' => 3,
            'flag_armado' => 0,
            'flag_requiere_proveedor' => 0,
            'ind_activo' => 1,
        ]);
        $tipoEnvio = TipoEnvio::create([
            'nombre' => 'Terrestre',
            'descripcion' => 'Prueba',
            'dias_envio' => 2,
        ]);

        $plan = $planner->calculate('2026-04-10', $producto->id, $tipoEnvio->id);

        $this->assertNotNull($plan);
        $this->assertSame('2026-04-08', $plan['fecha_embarque']);
        $this->assertSame('2026-04-03', $plan['fecha_produccion']);
        $this->assertNull($plan['mensaje_produccion']);
    }
}
