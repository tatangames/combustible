<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnterioresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anteriores', function (Blueprint $table) {
            $table->id();
            $table->integer('factura');
            $table->string('equipo', 400);
            $table->date('fecha');
            $table->string('producto', 1);
            $table->decimal('cantidad', 7,3);
            $table->decimal('unitario', 6, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('anteriores');
    }
}
