<?php

namespace Database\Factories;
use App\Models\User;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class sucursal_userFactory extends Factory
{
    // No hay un modelo asociado directamente; se usa para poblar la tabla pivote
    // En Laravel 10 no se suelen usar factories para pivotes, sino attach() directamente.
    
    public function definition(): array
    {
        return [
            'sucursal_id' => Sucursal::factory(),
            'user_id'     => User::factory(), 
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
