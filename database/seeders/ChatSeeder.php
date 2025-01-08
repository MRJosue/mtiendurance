<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Chat;
use App\Models\Proyecto;


class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                // Iterar por cada proyecto existente y generar un chat
                Proyecto::all()->each(function ($proyecto) {
                    Chat::factory()->create([
                        'proyecto_id' => $proyecto->id,
                    ]);
                });
    }
}
