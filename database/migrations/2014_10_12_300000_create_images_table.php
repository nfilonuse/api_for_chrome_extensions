<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->smallInteger('type')->default(0)->unsigned();
            $table->string('mimetype',50);
            $table->string('file_name');
            $table->smallInteger('accepted')->default(0)->unsigned()->index();
            $table->string('comment')->nullable();
            $table->timestamps();
        });

        Schema::table('images', function (Blueprint $table) {
	    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('images');
    }
}
