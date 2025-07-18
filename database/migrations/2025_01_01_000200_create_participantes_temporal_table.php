<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participantes_temporal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inscripcion_id');
            $table->unsignedBigInteger('created_by_id');
            $table->integer('tipo_inscripcion');
            $table->string('tipo_documento');
            $table->string('numero_documento');
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('nacionalidad');
            $table->string('genero');
            $table->date('fecha_nacimiento');
            $table->string('categoria');
            $table->string('talla');
            $table->string('celular');
            $table->string('email');
            $table->string('direccion');
            $table->string('provincia');
            $table->string('ciudad');
            $table->string('emergencia_nombre');
            $table->string('emergencia_celular');
            $table->string('parroquia');
            $table->timestamps();
            $table->foreign('inscripcion_id')->references('id')->on('inscripciones');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participantes_temporal');
    }
};
