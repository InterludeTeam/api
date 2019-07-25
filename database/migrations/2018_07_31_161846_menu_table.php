<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MenuTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('menu', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('position');
            $table->integer('price');
            $table->text('title');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('menu');
//        Schema::table('menu', function (Blueprint $table) {
//            //
//        });
    }
}
