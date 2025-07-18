<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturacion_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facturacion_id');
            $table->unsignedBigInteger('participante_id');
            $table->string('concepto');
            $table->decimal('valor', 10, 2);
            $table->timestamps();
            $table->foreign('facturacion_id')->references('id')->on('facturacion');
            $table->foreign('participante_id')->references('id')->on('participantes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturacion_detalles');
    }
};
