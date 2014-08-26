<?php

class Models_PlanetTest extends TestCase
{

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        Planet::boot();
        Player::boot();
        Game::boot();
        NavigationRoute::boot();
    }

    /**
     * Are we using Ardent?
     *
     * @test
     */
    public function testUsingArdent()
    {
        $planet = new Planet();
        $this->assertInstanceOf('LaravelBook\Ardent\Ardent', $planet);
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
        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        $player = Player::firstOrCreate(array('user_id' => $user->id, 'game_id' => $game->id));

        $expected = array(
            'planet_type' => 'sol',
            'x_position'  => null,
            'y_position'  => null,
        );

        $planet = Planet::firstOrCreate(array('player_id' => $player->id));

        $this->assertEquals($expected['planet_type'], $planet->planetType);
        $this->assertEquals($expected['x_position'], $planet->xPosition);
        $this->assertEquals($expected['y_position'], $planet->yPosition);
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
        $player = Player::firstOrCreate(array('user_id' => $user->id, 'game_id' => $game->id));

        $data = array(
            'player_id'      => $player->id,
            'planet_type' => 'venus',
            'x_position'  => 2,
            'y_position'  => 3,
        );
        $planet = Planet::firstOrCreate($data);

        $loaded = Planet::find($planet->id);
        $this->assertEquals($data['planet_type'], $loaded->planetType);
        $this->assertEquals($player->id, $loaded->player->id);
        $this->assertEquals($player->id, $loaded->player_id);
        $this->assertEquals($data['x_position'], $loaded->xPosition);
        $this->assertEquals($data['y_position'], $loaded->yPosition);
    }

    /**
     * Test which fields are required.
     *
     * @test
     */
    public function testFieldsRequired()
    {
        $planet = new Planet();
        $this->assertFalse($planet->save());

        $errors = $planet->errors()->all();
        $this->assertCount(2, $errors);

        $expected = array(
            'The player id field is required.',
            'The specified planet type is not available.',
        );
        $this->assertEquals($expected, $errors);

        unset($planet->player);

        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        $player = Player::firstOrCreate(array('user_id' => $user->id, 'game_id' => $game->id));
        $planet->player = $player;
        $planet->planetType = 'qawfasf';

        $this->assertFalse($planet->save());
        $errors = $planet->errors()->all();
        $this->assertCount(1, $errors);
        $expected = array(
            'The specified planet type is not available.',
        );
        $this->assertEquals($expected, $errors);

        $planet->planetType = 'terra';

        $this->assertTrue($planet->save());
        $errors = $planet->errors()->all();
        $this->assertCount(0, $errors);
    }

    /**
     * Test that planet XML Data is available and changing with the planet name.
     *
     * @test
     */
    public function testPlanetData()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        $player = Player::firstOrCreate(array('user_id' => $user->id, 'game_id' => $game->id));
        $planet = Planet::firstOrCreate(array('player_id' => $player->id));

        $this->assertInstanceOf('SimpleXMLElement', $planet->planet);
        $this->assertEquals('Sol', $planet->planet->name);
        $this->assertEquals('4', $planet->planet->routes);

        $planet->planetType = 'venus';
        $this->assertEquals('Venus', $planet->planet->name);
        $this->assertEquals('3', $planet->planet->routes);
    }

    /**
     * Test that routes are available.
     *
     * @test
     */
    public function testRoutesAssociation()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        $player = Player::firstOrCreate(array('user_id' => $user->id, 'game_id' => $game->id));
        $planet = Planet::firstOrCreate(array('player_id' => $player->id));

        $planet2 = Planet::create(array('player_id' => $player->id, 'planet_type' => 'mars'));
        $navigationroute = NavigationRoute::firstOrCreate(array('planet1_id' => $planet->id, 'planet2_id' => $planet2->id));

        $this->assertCount(1, $planet->routes);
        $this->assertEquals($navigationroute->id, $planet->routes->first()->id);
    }

    /**
     * Test that adjacent planets are available.
     *
     * @test
     */
    public function testAdjacentPlanets()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        $player = Player::firstOrCreate(array('user_id' => $user->id, 'game_id' => $game->id));
        $planet = Planet::firstOrCreate(array('player_id' => $player->id));

        $neighbors = $planet->adjacentPlanets;

        $this->assertCount(0, $neighbors);

        $planet2 = Planet::create(array('player_id' => $player->id, 'planet_type' => 'mars'));
        $navigationroute = NavigationRoute::firstOrCreate(array('planet1_id' => $planet->id, 'planet2_id' => $planet2->id));

        $neighbors = $planet->adjacentPlanets;
        $this->assertCount(1, $neighbors);

        $planet3 = Planet::create(array('player_id' => $player->id, 'planet_type' => 'venus'));
        $navigationroute = NavigationRoute::firstOrCreate(array('planet1_id' => $planet->id, 'planet2_id' => $planet3->id));

        $neighbors = $planet->adjacentPlanets;
        $this->assertCount(2, $neighbors);

        $planet4 = Planet::create(array('player_id' => $player->id, 'planet_type' => 'jupiter'));
        $navigationroute = NavigationRoute::firstOrCreate(array('planet1_id' => $planet2->id, 'planet2_id' => $planet4->id));

        $this->assertTrue($planet->isAdjacent($planet2));
        $this->assertFalse($planet->isAdjacent($planet4));
    }

    /**
     * Get Random Planet Types.
     *
     * @test
     */
    public function testGetRandomPlanetTypes()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::firstOrCreate(array('owner_id' => $user->id));

        $actual = Planet::getRandomPlanetTypes($game);
        $this->assertCount(12, $actual);
        $expected = array(
            'sol',
            'mercury',
            'venus',
            'terra',
            'luna',
            'mars',
            'jupiter',
            'europa',
            'saturn',
            'uranus',
            'neptune',
            'pluto',
        );
        $this->assertNotEquals($expected, $actual, 'Chance gave you an array of planets in the original order.');
        sort($actual);
        sort($expected);
        $this->assertEquals($expected, $actual);

        $actual = Planet::getRandomPlanetTypes($game, 4);
        $this->assertCount(4, $actual);

        $actual = Planet::getRandomPlanetTypes($game, 100);
        $this->assertCount(12, $actual);
        sort($actual);
        sort($expected);
        $this->assertEquals($expected, $actual);
    }

}
