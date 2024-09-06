<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturacionTable extends Migration
{
    /**
     * FACTURACION NUEVA
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facturacion', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('id_equipo')->unsigned();
            $table->bigInteger('id_tipocombustible')->unsigned();

            $table->string('numero_factura', 50);
            $table->date('fecha');
            $table->decimal('cantidad', 10,3);
            $table->decimal('unitario', 10,2);
            $table->string('km', 15)->nullable();

            $table->string('descripcion', 800)->nullable();


            // SE AGREGO TIPO DE FONDOS Y DISTRITO QUE SERAN NULL
            $table->bigInteger('id_fondos')->unsigned()->nullable();
            $table->bigInteger('id_distrito')->unsigned()->nullable();

            $table->foreign('id_equipo')->references('id')->on('equipos');
            $table->foreign('id_tipocombustible')->references('id')->on('tipocombustible');


            $table->foreign('id_fondos')->references('id')->on('tipo_fondos');
            $table->foreign('id_distrito')->references('id')->on('distritos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facturacion');
    }
}
