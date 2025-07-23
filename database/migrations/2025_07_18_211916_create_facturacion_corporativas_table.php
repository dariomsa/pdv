<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturacionCorporativasTable extends Migration
{
    public function up()
    {
        Schema::create('facturacion_corporativas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento');
            $table->string('numero_documento');
            $table->string('razon_social');
            $table->string('empresa');
            $table->string('email');
            $table->string('telefono');
            $table->string('direccion');
            $table->text('nota_adicional')->nullable();
            $table->unsignedBigInteger('forma_pago_id');
            $table->string('referencia')->nullable();
            $table->string('archivo_excel')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('facturacion_corporativas');
    }
}
