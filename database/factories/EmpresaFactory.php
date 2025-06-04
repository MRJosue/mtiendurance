<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Empresa;

class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    public function definition(): array
    {
        return [
            'nombre'    => $this->faker->company,
            'rfc'       => strtoupper($this->faker->bothify('????######')),
            'telefono'  => $this->faker->phoneNumber,
            'direccion' => $this->faker->address,
            'created_at'=> now(),
            'updated_at'=> now(),
        ];
    }
}
