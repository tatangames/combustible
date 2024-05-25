<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTipocombustibleTable extends Migration
{
    /**
     * TIPO DE COMBUSTIBLE
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tipocombustible', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tipocombustible');
    }
}
