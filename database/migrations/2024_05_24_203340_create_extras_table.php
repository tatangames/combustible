<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtrasTable extends Migration
{
    /**
     * SOLO ES 1 FILA,
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extras', function (Blueprint $table) {
            $table->id();

            $table->string('nombre1', 200)->nullable();
            $table->string('nombre2', 200)->nullable();
            $table->string('nombre3', 200)->nullable();
            $table->string('nombre4', 200)->nullable();

            $table->string('nombre_gasolinera', 200)->nullable();

            // Cuando se usa MPDF hay un uso local y un Servidor
            $table->boolean('reporte');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extras');
    }
}
