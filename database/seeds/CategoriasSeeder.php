<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categorias')->insert([
            ['id' => 1, 'nombre' => 'De 15 a 19 Años', 'edad_min' => 15, 'edad_max' => 19],
            ['id' => 2, 'nombre' => 'De 20 a 29 Años', 'edad_min' => 20, 'edad_max' => 29],
            ['id' => 3, 'nombre' => 'De 30 a 39 Años', 'edad_min' => 30, 'edad_max' => 39],
            ['id' => 4, 'nombre' => 'De 40 a 49 Años', 'edad_min' => 40, 'edad_max' => 49],
            ['id' => 5, 'nombre' => 'De 50 a 59 Años', 'edad_min' => 50, 'edad_max' => 59],
            ['id' => 6, 'nombre' => 'De 60 Años en Adelante', 'edad_min' => 60, 'edad_max' => null]
        ]);
    }
}
