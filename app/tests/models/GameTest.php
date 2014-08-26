<?php

class Models_GameTest extends TestCase
{

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        Game::boot();
    }

    /**
     * Are we using Ardent?
     *
     * @test
     */
    public function testUsingArdent()
    {
        $game = new Game();
        $this->assertInstanceOf('LaravelBook\Ardent\Ardent', $game);
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
            'name'             => 'StarBoard',
            'type'             => 'starboard',
            'state'            => 0,
            'max_players'      => 6,
            'active_player_id' => null,
        );

        $game = Game::create(array('owner' => $user));

        $this->assertEquals($expected['name'], $game->name);
        $this->assertEquals($expected['type'], $game->type);
        $this->assertEquals($expected['state'], $game->state);
        $this->assertEquals($expected['max_players'], $game->maxPlayers);
        $this->assertEquals($expected['active_player_id'], $game->activePlayerId);
        $this->assertEquals($expected['active_player_id'], $game->activePlayer);
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
        $data = array(
            'owner'       => $user,
            'name'        => 'Testgame',
            'type'        => 'starboard',
            'state'       => 2,
            'max_players' => 4,
        );
        $game = Game::create($data);

        $loaded = Game::find($game->id);
        $this->assertEquals($data['name'], $loaded->name);
        $this->assertEquals($data['type'], $loaded->type);
        $this->assertEquals($user->id, $loaded->owner->id);
        $this->assertEquals($user->id, $loaded->owner_id);
        $this->assertEquals($data['state'], $loaded->state);
        $this->assertEquals($data['max_players'], $loaded->maxPlayers);
    }

    /**
     * Is the owner association working?
     *
     * @test
     */
    public function testOwnerAssociation()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = new Game();
        $game->owner = $user;
        $game->save();

        $this->assertInstanceOf('User', $game->owner);
        $this->assertEquals($user->id, $game->owner->id);
        $this->assertEquals($user->id, $game->owner_id);
    }

    /**
     * Test which fields are required.
     *
     * @test
     */
    public function testFieldsRequired()
    {
        $game = new Game();
        $this->assertFalse($game->save());

        $errors = $game->errors()->all();
        $this->assertCount(1, $errors);

        $expected = array(
            'The owner id field is required.',
        );
        $this->assertEquals($expected, $errors);

        $game->type = 'name#+ü';

        $this->assertFalse($game->save());

        $errors = $game->errors()->all();
        $this->assertCount(2, $errors);

        $expected = array(
            'The owner id field is required.',
            'The specified type is not available.',
        );
        $this->assertEquals($expected, $errors);

        $game->type = 'starboard';
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game->owner = $user;
        $this->assertTrue($game->save());

        $errors = $game->errors()->all();
        $this->assertCount(0, $errors);
    }

    /**
     * Test User associations through players.
     *
     * @test
     */
    public function testUserAssocicationsThroughPlayers()
    {
        $owner = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));

        $game = Game::firstOrCreate(array('owner_id' => $owner->id));

        $this->assertCount(0, $game->users);

        $player = Player::create(array('user' => $owner, 'game' => $game));
        $game = Game::find($game->id);
        $this->assertCount(1, $game->users);
        $user = $game->users()->first();
        $this->assertInstanceOf('User', $user);
        $this->assertEquals($user->id, $owner->id);

        $this->assertCount(1, $game->players);
        $player = $game->players()->first();
        $this->assertInstanceOf('Player', $player);
        $this->assertEquals($player->user->id, $owner->id);
    }

    /**
     * Get Game config for type.
     *
     * @test
     */
    public function testGetGameConfig()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));

        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        $actual = $game->config;
        $this->assertInstanceOf('SimpleXMLElement', $actual);

        $actual = $game->config->name;
        $expected = 'StarBoard';
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test state scope.
     *
     * @test
     */
    public function testGetGamesInState()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));

        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        /* @var $game Game */

        $stateGames = Game::state(Game::STATE_SETUP)->get();
        $this->assertCount(1, $stateGames);
        $this->assertEquals($game->id, $stateGames->first()->id);
        $stateGames = Game::state(Game::STATE_OPEN)->get();
        $this->assertCount(0, $stateGames);

        $game->state = Game::STATE_OPEN;
        $game->save();

        $stateGames = Game::state(Game::STATE_SETUP)->get();
        $this->assertCount(0, $stateGames);
        $stateGames = Game::state(Game::STATE_OPEN)->get();
        $this->assertCount(1, $stateGames);
        $this->assertEquals($game->id, $stateGames->first()->id);

        $stateGames = Game::state(array(Game::STATE_OPEN, Game::STATE_SETUP))->get();
        $this->assertCount(1, $stateGames);
        $this->assertEquals($game->id, $stateGames->first()->id);

        $game->state = 2;
        $game->save();
        $stateGames = Game::state(array(Game::STATE_OPEN, Game::STATE_SETUP))->get();
        $this->assertCount(0, $stateGames);
    }

    /**
     * Test open scope.
     *
     * @test
     */
    public function testGetOpenGames()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));

        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        /* @var $game Game */

        $openGames = Game::open()->get();
        $this->assertCount(0, $openGames);

        $game->state = Game::STATE_OPEN;
        $game->save();

        $openGames = Game::open()->get();
        $this->assertCount(1, $openGames);
        $this->assertEquals($game->id, $openGames->first()->id);
    }

    /**
     * What's the default setting for maxPlayers?
     *
     * @test
     */
    public function testGetDefaultMaxPlayers()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));

        $game = Game::create(array('owner' => $user, 'max_players' => 4,));

        $this->assertEquals(6, $game->defaultMaxPlayers);
    }

    /**
     * What's the default setting for maxPlayers?
     *
     * @test
     */
    public function testGetAvailableTypes()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));

        $game = Game::create(array('owner' => $user,));

        $expected = array(
            'starboard' => 'StarBoard',
        );
        $this->assertEquals($expected, $game->availableTypes);
    }

    /**
     * Can we get the next faction that is not used by a player?
     * Defaults to first faction if all are used.
     *
     * @test
     */
    public function testGetNextFactionType()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::create(array('owner' => $user,));
        $this->assertEquals('jack', $game->nextFactionType);

        $player = Player::create(array('user' => $user, 'game' => $game));
        unset($game->players);
        $this->assertEquals('minsk', $game->nextFactionType);

        $user = User::firstOrCreate(array(
                'email'                 => 'foo2@bar.com',
                'name'                  => 'foobar2',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $player = Player::create(array('user' => $user, 'game' => $game, 'faction_type' => $game->nextFactionType));
        unset($game->players);
        $this->assertEquals('tirius', $game->nextFactionType);

        $user = User::firstOrCreate(array(
                'email'                 => 'foo3@bar.com',
                'name'                  => 'foobar3',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $player = Player::create(array('user' => $user, 'game' => $game, 'faction_type' => 'alarus'));
        unset($game->players);
        $this->assertEquals('tirius', $game->nextFactionType);

        $user = User::firstOrCreate(array(
                'email'                 => 'foo4@bar.com',
                'name'                  => 'foobar4',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $player = Player::create(array('user' => $user, 'game' => $game, 'faction_type' => 'tirius'));
        $user = User::firstOrCreate(array(
                'email'                 => 'foo5@bar.com',
                'name'                  => 'foobar5',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $player = Player::create(array('user' => $user, 'game' => $game, 'faction_type' => 'queen'));
        unset($game->players);
        $this->assertEquals('brain', $game->nextFactionType);

        $user = User::firstOrCreate(array(
                'email'                 => 'foo6@bar.com',
                'name'                  => 'foobar6',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $player = Player::create(array('user' => $user, 'game' => $game, 'faction_type' => 'brain'));
        unset($game->players);
        $this->assertEquals('jack', $game->nextFactionType);
    }

    /**
     * Is the active player association working?
     *
     * @test
     */
    public function testActivePlayerAssociation()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = new Game();
        $game->owner = $user;
        $game->save();

        $player = Player::create(array('user' => $user, 'game' => $game));

        $game->activePlayer = $player;
        $game->save();

        $game = Game::find($game->id);

        $this->assertInstanceOf('Player', $game->activePlayer);
        $this->assertEquals($player->id, $game->activePlayer->id);
        $this->assertEquals($player->id, $game->active_player_id);
    }

}
