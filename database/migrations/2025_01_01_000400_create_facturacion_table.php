<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inscripcion_id');
            $table->decimal('total', 10, 2);
            $table->decimal('iva', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
            $table->foreign('inscripcion_id')->references('id')->on('inscripciones');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturacion');
    }
};
