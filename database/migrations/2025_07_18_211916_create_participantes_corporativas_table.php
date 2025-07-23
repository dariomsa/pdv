<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParticipantesCorporativasTable extends Migration
{
    public function up()
    {
        Schema::create('participantes_corporativas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inscripcion_id')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->string('tipo_documento')->nullable();
            $table->string('numero_documento')->nullable();
            $table->string('nombres')->nullable();
            $table->string('apellidos')->nullable();
            $table->string('nacionalidad')->nullable();
            $table->string('genero')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('categoria')->nullable();
            $table->string('talla')->nullable();
            $table->string('celular')->nullable();
            $table->string('email')->nullable();
            $table->string('direccion')->nullable();
            $table->string('provincia')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('parroquia')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('participantes_corporativas');
    }
}
