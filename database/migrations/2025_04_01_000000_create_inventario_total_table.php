<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_total', function (Blueprint $table) {
            $table->id();
            $table->string('talla', 10)->nullable();
            $table->integer('stock_total')->default(0);
            $table->integer('stock_restante')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_total');
    }
};
