<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContratosDetalleTable extends Migration
{
    /**
     * CONTRATOS DETALLE
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contratos_detalle', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_contratos')->unsigned();
            $table->bigInteger('id_distrito')->unsigned();
            $table->bigInteger('id_combustible')->unsigned();
            $table->bigInteger('id_unidad')->unsigned();

            $table->string('codigo', 50)->nullable();

            $table->foreign('id_contratos')->references('id')->on('contratos');
            $table->foreign('id_distrito')->references('id')->on('distritos');
            $table->foreign('id_combustible')->references('id')->on('tipocombustible');
            $table->foreign('id_unidad')->references('id')->on('unidad_medida');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contratos_detalle');
    }
}
