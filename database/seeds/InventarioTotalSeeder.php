<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventarioTotalSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('inventario_total')->insert([
            ['id' => 1, 'talla' => 'XS', 'stock_total' => 100, 'stock_restante' => 100],
            ['id' => 2, 'talla' => 'S', 'stock_total' => 100, 'stock_restante' => 100],
            ['id' => 3, 'talla' => 'M', 'stock_total' => 100, 'stock_restante' => 100],
            ['id' => 4, 'talla' => 'L', 'stock_total' => 100, 'stock_restante' => 100],
            ['id' => 5, 'talla' => 'XL', 'stock_total' => 100, 'stock_restante' => 100]
        ]);
    }
}
