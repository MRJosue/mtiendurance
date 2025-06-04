<?php

namespace Database\Factories;

use App\Models\Empresa;       // ← Debe apuntar a App\Models\Empresa
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresaFactory extends Factory
{


    protected $model = Empresa::class;  // ← Debe apuntar a la clase correcta

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