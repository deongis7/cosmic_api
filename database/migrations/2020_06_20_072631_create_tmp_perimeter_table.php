<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTmpPerimeterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tmp_perimeter', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('region', 150);
			$table->string('perimeter', 255);
			$table->string('k_perimeter', 255);
			$table->string('pic', 255);
			$table->string('nik_pic', 50);
			$table->string('level', 2);
			$table->string('fo', 255);
			$table->string('nik_fo', 50);
			$table->string('keterangan', 255)->nullable();
			$table->string('kd_perusahaan', 255)->nullable();
			$table->string('longitude', 30)->nullable();
			$table->string('latitude', 30)->nullable();
			$table->smallInteger('status')->default(0)->nullable();

			$table->string('c1', 2)->default(null)->nullable();
			$table->string('c2', 2)->default(null)->nullable();
			$table->string('c3', 2)->default(null)->nullable();
			$table->string('c4', 2)->default(null)->nullable();
			$table->string('c5', 2)->default(null)->nullable();
			$table->string('c6', 2)->default(null)->nullable();
			$table->string('c7', 2)->default(null)->nullable();
			$table->string('c8', 2)->default(null)->nullable();
			$table->string('c9', 2)->default(null)->nullable();
			$table->string('c10', 2)->default(null)->nullable();
			$table->string('c11', 2)->default(null)->nullable();
			$table->string('c12', 2)->default(null)->nullable();
			$table->string('c13', 2)->default(null)->nullable();
			$table->string('c14', 2)->default(null)->nullable();
			$table->string('c15', 2)->default(null)->nullable();
			$table->string('c16', 2)->default(null)->nullable();
			$table->string('c17', 2)->default(null)->nullable();
			$table->string('c18', 2)->default(null)->nullable();
			$table->string('c19', 2)->default(null)->nullable();
			$table->string('c20', 2)->default(null)->nullable();
			$table->string('c21', 2)->default(null)->nullable();
			$table->string('c22', 2)->default(null)->nullable();
			$table->string('c23', 2)->default(null)->nullable();
			$table->string('c24', 2)->default(null)->nullable();
			$table->string('c25', 2)->default(null)->nullable();

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
        Schema::dropIfExists('tmp_perimeter');
    }
}
