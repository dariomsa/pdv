<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pago_id');
            $table->unsignedBigInteger('participante_id');
            $table->decimal('valor_pagado', 10, 2);
            $table->timestamps();
            $table->foreign('pago_id')->references('id')->on('pagos');
            $table->foreign('participante_id')->references('id')->on('participantes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_detalles');
    }
};
