<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSocnetworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_socnetworks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->smallInteger('social_type')->default(1)->comment('1 - facebook; 2 - linkedIn; 3 - Google; 4 - VK');
            $table->string('social_id',50)->nullable();
            $table->timestamps();
        });
        Schema::table('user_socnetworks', function (Blueprint $table) {
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
        Schema::dropIfExists('user_socnetworks');
    }
}
