<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameCharacterTypeToFactionTypeOnPlayersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('players', function ($table) {
            $table->renameColumn('character_type', 'faction_type');
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
            $table->renameColumn('faction_type', 'character_type');
        });
	}

}
