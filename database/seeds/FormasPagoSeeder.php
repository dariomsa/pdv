<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormasPagoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('formas_pago')->insert([
            ['id' => 1, 'metodo_pago' => 'Diners Club', 'estado' => 'A', 'tipo_pago' => 'Tarjeta'],
            ['id' => 2, 'metodo_pago' => 'Discover', 'estado' => 'A', 'tipo_pago' => 'Tarjeta'],
            ['id' => 3, 'metodo_pago' => 'MasterCard', 'estado' => 'A', 'tipo_pago' => 'Tarjeta'],
            ['id' => 4, 'metodo_pago' => 'Visa', 'estado' => 'A', 'tipo_pago' => 'Tarjeta'],
            ['id' => 5, 'metodo_pago' => 'Efectivo', 'estado' => 'A', 'tipo_pago' => 'Efectivo'],
            ['id' => 6, 'metodo_pago' => 'Deuna', 'estado' => 'A', 'tipo_pago' => 'Deuna'],
            ['id' => 7, 'metodo_pago' => 'American Express', 'estado' => 'A', 'tipo_pago' => 'Tarjeta'],
            ['id' => 8, 'metodo_pago' => 'Transferencia', 'estado' => 'A', 'tipo_pago' => 'Transferencia'],
            ['id' => 9, 'metodo_pago' => 'Depósito bancario', 'estado' => 'A', 'tipo_pago' => 'Deposito'],
            ['id' => 10, 'metodo_pago' => 'Otra Tarjeta', 'estado' => 'A', 'tipo_pago' => 'Tarjeta']
        ]);
    }
}
