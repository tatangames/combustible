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
            $table->integer('factura');
            $table->date('fecha');
            $table->string('producto', 1);
            $table->decimal('cantidad', 7,3);
            $table->decimal('unitario', 6, 2);
            $table->boolean('visible');

            $table->foreign('id_equipo')->references('id')->on('equipos');
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
