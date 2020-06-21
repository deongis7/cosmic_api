<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_user', function (Blueprint $table) {
			$table->bigIncrements('mu_id');
			$table->string('mu_username', 50);
			$table->string('mu_password', 255);
			$table->string('mu_nik',20)->default(null)->nullable();
			
			$table->string('mu_name', 255)->default(null)->nullable();
			$table->mediumInteger('mu_mc_id')->default(null)->nullable();
			$table->mediumInteger('mu_mr_id')->default(null)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_user');
    }
}
