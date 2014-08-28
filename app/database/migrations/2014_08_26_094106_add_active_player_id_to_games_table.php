<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddActivePlayerIdToGamesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('games', function(Blueprint $table)
		{
			$table->integer('active_player_id')->unsigned()->nullable()->index();
            $table->foreign('active_player_id')->references('id')->on('players')->onDelete('set null');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('games', function(Blueprint $table)
		{
            /* @var $table Illuminate\Database\Schema\Blueprint */
            $table->dropForeign('games_active_player_id_foreign');
			$table->dropColumn('active_player_id');
		});
	}

}
