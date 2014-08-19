<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqeUserIdGameIdConstraintToPlayersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('players', function ($table) {
            $table->unique(array('user_id', 'game_id'));
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('players', function ($table) {
            $table->dropUnique('players_user_id_game_id_unique');
        });
	}

}
