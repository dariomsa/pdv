<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cierre_caja_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cierre_caja_id');
            $table->unsignedBigInteger('forma_pago_id');
            $table->decimal('monto', 10, 2);
            $table->timestamps();
            $table->foreign('cierre_caja_id')->references('id')->on('cierre_caja');
            $table->foreign('forma_pago_id')->references('id')->on('forma_pagos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cierre_caja_detalles');
    }
};
