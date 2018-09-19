<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('patent_number',100);
            $table->string('patent_name',100);
            $table->integer('patent_count_page');
            $table->integer('patent_count_column');
            $table->string('patent_number_sys_id',100);
            $table->timestamps();
        });
        Schema::table('patents', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('patent_number');
            $table->index('patent_number_sys_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patents');
    }
}
