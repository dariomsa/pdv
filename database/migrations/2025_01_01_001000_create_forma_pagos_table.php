<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forma_pagos', function (Blueprint $table) {
            $table->id();
            $table->string('metodo_pago');
            $table->boolean('estado')->default(true);
            $table->string('tipo_pago');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formapagos');
    }
};
