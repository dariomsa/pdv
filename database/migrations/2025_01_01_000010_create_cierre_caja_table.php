<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cierre_caja', function (Blueprint $table) {
            $table->id(); // bigint(20)
            $table->date('fecha');
            $table->bigInteger('secuencia');
            $table->unsignedBigInteger('id_punto_servicio');
            $table->double('monto_total');
            $table->integer('numero_facturas');
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by_id');

            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_punto_servicio')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('cierre_caja');
    }
};
