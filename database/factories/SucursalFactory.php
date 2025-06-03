<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Sucursal;
use App\Models\Empresa;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sucursal>
 */
class SucursalFactory extends Factory
{
    protected $model = Sucursal::class;

    public function definition(): array
    {
        return [
            // Por defecto asocia a una empresa ya existente o crea una nueva si no se pasa empresa_id
            'empresa_id' => Empresa::factory(),
            'nombre'     => $this->faker->companySuffix . ' Sucursal',
            'telefono'   => $this->faker->phoneNumber,
            'direccion'  => $this->faker->address,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Permite crear sucursales vinculadas explícitamente a una empresa
     */
    public function forEmpresa(int $empresaId): self
    {
        return $this->state(fn(array $attributes) => [
            'empresa_id' => $empresaId,
        ]);
    }
}
