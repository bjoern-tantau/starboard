<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlanetsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('planets', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('player_id')->unsigned()->index();
            $table->foreign('player_id')->references('id')->on('players')->onDelete('cascade');
			$table->string('planet_type');
			$table->integer('x_position')->nullable();
			$table->integer('y_position')->nullable();
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
		Schema::drop('planets');
	}

}
