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
            'name'        => 'StarBoard',
            'type'        => 'starboard',
            'state'       => 0,
            'max_players' => 6,
        );

        $game = Game::create(array('owner' => $user));

        $this->assertEquals($expected['name'], $game->name);
        $this->assertEquals($expected['type'], $game->type);
        $this->assertEquals($expected['state'], $game->state);
        $this->assertEquals($expected['max_players'], $game->maxPlayers);
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

        $game = Game::firstOrCreate(array('owner' => $owner));

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

        $game = Game::firstOrCreate(array('owner' => $user));
        $actual = $game->config;
        $this->assertInstanceOf('SimpleXMLElement', $actual);

        $actual = $game->config->name;
        $expected = 'StarBoard';
        $this->assertEquals($expected, $actual);
    }

}
