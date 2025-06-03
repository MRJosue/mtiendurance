<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Empresa;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empresa>
 */
class EmpresaFactory extends Factory
{
    // El modelo al que corresponde esta factory
    protected $model = Empresa::class;

    public function definition(): array
    {
        return [
            'nombre'    => $this->faker->company,
            'rfc'       => strtoupper($this->faker->bothify('????######')), // ejemplo de RFC alfanumérico
            'telefono'  => $this->faker->phoneNumber,
            'direccion' => $this->faker->address,
            'created_at'=> now(),
            'updated_at'=> now(),
        ];
    }
}
