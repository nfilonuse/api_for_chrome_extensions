<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email',100)->unique();
            $table->string('password');
            $table->string('company_id');
            $table->integer('is_active')->default(1)->unsigned();
            $table->integer('role_id')->default(100);
            $table->integer('agreement_flag')->nullable()->default(0);
            $table->dateTime('agreement_time')->nullable();
            $table->string('first_name',100)->nullable()->default('');
            $table->string('middle_name',100)->nullable()->default('');
            $table->string('last_name',100)->nullable()->default('');
            $table->date('date_birthday')->nullable();
            $table->string('phone',16)->nullable()->default('');
            $table->integer('country_id')->nullable()->default(0);
            $table->integer('state_id')->nullable()->default(0);
            $table->string('city',100)->nullable()->default('');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table) {
	        $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
	        $table->foreign('role_id')->references('id')->on('roles');
	        $table->index('country_id');
	        $table->index('state_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
