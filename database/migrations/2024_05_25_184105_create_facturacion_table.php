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
            $table->decimal('unitario', 10,3);
            $table->string('km', 15)->nullable();

            $table->foreign('id_equipo')->references('id')->on('equipos');
            $table->foreign('id_tipocombustible')->references('id')->on('tipocombustible');
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
