<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNavigationRoutesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('navigation_routes', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('planet1_id')->unsigned()->index();
            $table->foreign('planet1_id')->references('id')->on('planets')->onDelete('cascade');
			$table->integer('planet2_id')->nullable()->unsigned()->index();
            $table->foreign('planet2_id')->references('id')->on('planets')->onDelete('cascade');
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
		Schema::drop('navigation_routes');
	}

}
