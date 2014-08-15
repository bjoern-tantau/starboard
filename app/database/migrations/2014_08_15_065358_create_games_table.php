<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGamesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('games', function(Blueprint $table)
		{
            /* @var $table Blueprint */
			$table->increments('id');
			$table->string('name');
			$table->string('type');
			$table->integer('owner_id')->unsigned()->index();
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
			$table->tinyInteger('state');
			$table->integer('max_players')->unsigned();
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
		Schema::drop('games');
	}

}
