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
        $game = Game::firstOrCreate(array('owner' => $user));
        $player = Player::firstOrCreate(array('user' => $user, 'game' => $game));
        $planet1 = Planet::firstOrCreate(array('player' => $player));
        $planet2 = Planet::create(array('player' => $player, 'planet_type' => 'mars'));

        $data = array(
            'planet1' => $planet1,
            'planet2' => $planet2,
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
        $game = Game::firstOrCreate(array('owner' => $user));
        $player = Player::firstOrCreate(array('user' => $user, 'game' => $game));
        $planet1 = Planet::firstOrCreate(array('player' => $player));

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

        $planet2 = Planet::create(array('player' => $player, 'planet_type' => 'mars'));
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
        $game = Game::firstOrCreate(array('owner' => $user));
        $player = Player::firstOrCreate(array('user' => $user, 'game' => $game));
        $planet1 = Planet::firstOrCreate(array('player' => $player));
        $planet2 = Planet::create(array('player' => $player, 'planet_type' => 'mars'));

        $navigationroute = NavigationRoute::firstOrCreate(array('planet1' => $planet1));

        $planets = $navigationroute->planets;

        $this->assertEquals($planets->first()->id, $planet1->id);
        $this->assertCount(1, $planets);

        $navigationroute = NavigationRoute::firstOrCreate(array('planet1' => $planet1, 'planet2' => $planet2));
        $planets = $navigationroute->planets;
        $this->assertCount(2, $planets);
    }

}
