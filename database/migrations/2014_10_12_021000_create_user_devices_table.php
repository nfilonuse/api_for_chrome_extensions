<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->smallInteger('device_type')->default(0)->unsigned();
            $table->string('gcm_id')->nullable();
            $table->string('lang',5)->default('en');
            $table->string('device_key')->nullable();
            $table->timestamps();
        });
        Schema::table('user_devices', function (Blueprint $table) {
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
        Schema::dropIfExists('user_devices');
    }
}
