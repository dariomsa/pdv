<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by_id');
            $table->integer('estado')->default(0);
            $table->unsignedBigInteger('id_cierre_caja')->nullable();
            $table->timestamps();
            $table->foreign('created_by_id')->references('id')->on('users');
            $table->foreign('id_cierre_caja')->references('id')->on('cierre_caja');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
