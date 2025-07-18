<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inscripcion_id');
            $table->unsignedBigInteger('facturacion_id');
            $table->decimal('monto_total', 10, 2);
            $table->timestamps();
            $table->foreign('inscripcion_id')->references('id')->on('inscripciones');
            $table->foreign('facturacion_id')->references('id')->on('facturacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
