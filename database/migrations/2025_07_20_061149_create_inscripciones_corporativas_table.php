<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInscripcionesCorporativasTable extends Migration
{
    public function up()
    {
        Schema::create('inscripciones_corporativas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento');
            $table->string('numero_documento');
            $table->string('razon_social');
            $table->string('empresa');
            $table->string('email');
            $table->string('telefono');
            $table->string('direccion');
            $table->text('nota_adicional')->nullable();
            $table->string('archivo_excel')->nullable();
            $table->unsignedBigInteger('forma_pago_id')->nullable();
            $table->string('referencia')->nullable();
            $table->timestamps();

            $table->foreign('forma_pago_id')->references('id')->on('formas_pago')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inscripciones_corporativas');
    }
}
