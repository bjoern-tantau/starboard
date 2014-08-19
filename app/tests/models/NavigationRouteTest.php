<?php

class Models_NavigationRouteTest extends TestCase
{

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        NavigationRoute::boot();
        Planet::boot();
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
        $navigationroute = new NavigationRoute();
        $this->assertInstanceOf('LaravelBook\Ardent\Ardent', $navigationroute);
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
        $planet1 = Planet::firstOrCreate(array('player_id' => $player->id));
        $planet2 = Planet::create(array('player_id' => $player->id, 'planet_type' => 'mars'));

        $data = array(
            'planet1_id' => $planet1->id,
            'planet2_id' => $planet2->id,
        );
        $navigationroute = NavigationRoute::firstOrCreate($data);

        $loaded = NavigationRoute::find($navigationroute->id);
        $this->assertEquals($planet1->id, $navigationroute->planet1->id);
        $this->assertEquals($planet2->id, $navigationroute->planet2->id);
    }

    /**
     * Test which fields are required.
     *
     * @test
     */
    public function testFieldsRequired()
    {
        $navigationroute = new NavigationRoute();
        $this->assertFalse($navigationroute->save());

        $errors = $navigationroute->errors()->all();
        $this->assertCount(1, $errors);

        $expected = array(
            'The planet1 id field is required.',
        );
        $this->assertEquals($expected, $errors);

        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        $player = Player::firstOrCreate(array('user_id' => $user->id, 'game_id' => $game->id));
        $planet1 = Planet::firstOrCreate(array('player_id' => $player->id));

        $navigationroute->planet1 = $planet1;

        $this->assertTrue($navigationroute->save());
        $errors = $navigationroute->errors()->all();
        $this->assertCount(0, $errors);

        $navigationroute->planet2 = $planet1;
        $this->assertFalse($navigationroute->save());

        $errors = $navigationroute->errors()->all();
        $this->assertCount(1, $errors);

        $expected = array(
            'The planet2 id and planet1 id must be different.',
        );
        $this->assertEquals($expected, $errors);

        $planet2 = Planet::create(array('player_id' => $player->id, 'planet_type' => 'mars'));
        $navigationroute->planet2 = $planet2;

        $this->assertTrue($navigationroute->save());
        $errors = $navigationroute->errors()->all();
        $this->assertCount(0, $errors);
    }

    /**
     * Test planets association.
     *
     * @test
     */
    public function testPlanetsAssociation()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        $player = Player::firstOrCreate(array('user_id' => $user->id, 'game_id' => $game->id));
        $planet1 = Planet::firstOrCreate(array('player_id' => $player->id));
        $planet2 = Planet::create(array('player_id' => $player->id, 'planet_type' => 'mars'));

        $navigationroute = NavigationRoute::firstOrCreate(array('planet1_id' => $planet1->id));

        $planets = $navigationroute->planets;

        $this->assertEquals($planets->first()->id, $planet1->id);
        $this->assertCount(1, $planets);

        $navigationroute = NavigationRoute::firstOrCreate(array('planet1_id' => $planet1->id, 'planet2_id' => $planet2->id));
        $planets = $navigationroute->planets;
        $this->assertCount(2, $planets);
    }

}
