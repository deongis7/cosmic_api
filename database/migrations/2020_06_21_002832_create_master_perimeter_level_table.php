<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterPerimeterLevelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_perimeter_level', function (Blueprint $table) {
            $table->mediumIncrements('mpml_id');
			$table->string('mpml_name', 255)->default(null)->nullable();
			$table->mediumInteger('mpml_mpm_id')->default(null)->nullable();
			$table->string('mpm_mu_nik',20)->default(null)->nullable();
			$table->string('mpml_ket', 255)->default(null)->nullable();
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
        Schema::dropIfExists('master_perimeter_level');
    }
}
