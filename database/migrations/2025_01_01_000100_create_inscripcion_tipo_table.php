<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripcion_tipo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('valor', 8, 2);
            $table->decimal('iva', 4, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripcion_tipo');
    }
};
