<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterPerimeterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_perimeter', function (Blueprint $table) {
			$table->mediumIncrements('mpm_id');
			$table->string('mpm_name', 255)->default(null)->nullable();
			$table->mediumInteger('mpm_mr_id')->default(null)->nullable();
			$table->string('mpm_mu_nik',20)->default(null)->nullable();
			$table->mediumInteger('mpm_mpmk_id')->default(null)->nullable();
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
        Schema::dropIfExists('master_perimeter');
    }
}
