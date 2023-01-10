<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('factura', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_equipo')->unsigned();
            $table->bigInteger('id_tipocombustible')->unsigned();

            $table->integer('factura');
            $table->date('fecha');
            $table->decimal('cantidad', 7,3); // cantidad de combustible
            $table->decimal('unitario', 10, 2); // $

            $table->foreign('id_equipo')->references('id')->on('equipos');
            $table->foreign('id_tipocombustible')->references('id')->on('tipo_combustible');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('factura');
    }
}
