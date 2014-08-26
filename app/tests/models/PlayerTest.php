<?php

class Models_PlayerTest extends TestCase
{

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        Player::boot();
        Game::boot();
    }

    /**
     * Are we using Ardent?
     *
     * @test
     */
    public function testUsingArdent()
    {
        $player = new Player();
        $this->assertInstanceOf('LaravelBook\Ardent\Ardent', $player);
    }

    /**
     * What fields are available?
     *
     * @test
     */
    public function testDefaults()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $expected = array(
            'faction_type' => 'jack',
            'active'       => true,
        );

        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        $player = Player::firstOrCreate(array('user_id' => $user->id, 'game_id' => $game->id));

        $this->assertEquals($expected['faction_type'], $player->factionType);
        $this->assertEquals($expected['active'], $player->active);
    }

    /**
     * What fields are available?
     *
     * @test
     */
    public function testFieldsAvailable()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        $data = array(
            'user_id'      => $user->id,
            'game_id'      => $game->id,
            'faction_type' => 'minsk',
            'active'       => 1,
        );
        $player = Player::firstOrCreate($data);

        $loaded = Player::find($player->id);
        $this->assertEquals($data['faction_type'], $loaded->factionType);
        $this->assertEquals($data['active'], $loaded->active);
        $this->assertEquals($user->id, $loaded->user->id);
        $this->assertEquals($game->id, $loaded->game->id);
    }

    /**
     * Test which fields are required.
     *
     * @test
     */
    public function testFieldsRequired()
    {
        $player = new Player();
        $this->assertFalse($player->save());

        $errors = $player->errors()->all();
        $this->assertCount(3, $errors);

        $expected = array(
            'The user id field is required.',
            'The game id field is required.',
            'The specified faction type is not available.',
        );
        $this->assertEquals($expected, $errors);

        unset($player->game);

        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        $player->user = $user;
        $player->game = $game;
        $player->factionType = 'qawfasf';

        $this->assertFalse($player->save());
        $errors = $player->errors()->all();
        $this->assertCount(1, $errors);
        $expected = array(
            'The specified faction type is not available.',
        );
        $this->assertEquals($expected, $errors);

        $player->factionType = 'jack';

        $this->assertTrue($player->save());
        $errors = $player->errors()->all();
        $this->assertCount(0, $errors);
    }

    /**
     * Test that faction XML Data is available and changing with the faction name.
     *
     * @test
     */
    public function testFactionData()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        $player = Player::firstOrCreate(array('user_id' => $user->id, 'game_id' => $game->id));

        $this->assertInstanceOf('SimpleXMLElement', $player->faction);
        $this->assertEquals('Jack Reynolds', $player->faction->name);
        $this->assertEquals('humans', $player->faction->race);
        $this->assertEquals('blue', $player->faction->color);

        $player->factionType = 'queen';
        $this->assertEquals('The Queen of Knives', $player->faction->name);
        $this->assertEquals('aliens', $player->faction->race);
        $this->assertEquals('purple', $player->faction->color);

        $factions = $player->factions;
        $this->assertInstanceOf('SimpleXMLElement', $factions);
        $this->assertEquals(6, $factions->count());
        $this->assertEquals('Jack Reynolds', $factions->jack->name);

        $availableTypes = $player->availableFactionTypes;
        $expectedTypes = array(
            'jack'   => 'Jack Reynolds',
            'minsk'  => 'Antigus Minsk',
            'tirius' => 'Tirius',
            'alarus' => 'Alarus',
            'queen'  => 'The Queen of Knives',
            'brain'  => 'The Overbrain',
        );

        $this->assertEquals($expectedTypes, $availableTypes);
    }

    /**
     * Can a player be created by giving a game and user?
     *
     * @test
     */
    public function testCanFindAndCreatePlayer()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        $player1 = Player::firstOrCreate(array('user_id' => $user->id, 'game_id' => $game->id));

        $this->assertNotNull($player1->id);

        $player2 = Player::firstOrCreate(array('user_id' => $user->id, 'game_id' => $game->id));

        $this->assertEquals($player1->id, $player2->id);
    }

    /**
     * Test that only one user may have a player per game.
     *
     * @test
     */
    public function testUniqueUserPerGame()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::firstOrCreate(array('owner_id' => $user->id));

        $player = Player::create(array('user_id' => $user->id, 'game_id' => $game->id));

        $this->setExpectedException('\Illuminate\Database\QueryException');
        $player = Player::create(array('user_id' => $user->id, 'game_id' => $game->id));
    }

    /**
     * Test planets association.
     *
     * @test
     */
    public function testPlanetAssociation()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        $player = Player::create(array('user_id' => $user->id, 'game_id' => $game->id));
        $this->assertCount(0, $player->planets);

        $planet = Planet::create(array('player' => $player));
        $player = Player::find($player->id);
        $this->assertCount(1, $player->planets);
        $this->assertEquals($planet->id, $player->planets->first()->id);

        $planet = Planet::create(array('player' => $player));
        $player = Player::find($player->id);
        $this->assertCount(2, $player->planets);
        $this->assertNotEquals($planet->id, $player->planets->first()->id);
    }

}
