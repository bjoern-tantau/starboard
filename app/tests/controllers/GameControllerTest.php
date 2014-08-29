<?php

class Controllers_GameControllerTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        Route::enableFilters();
        Game::boot();
    }

    /**
     * Tests index.
     *
     * @test
     */
    public function testGetIndex()
    {
        $crawler = $this->client->request('GET', action('GameController@getIndex'));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));
        $user = new User(array(
            'name'                  => 'admin',
            'email'                 => 'foo@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            )
        );
        $user->save();
        Auth::login($user);
        $crawler = $this->client->request('GET', action('GameController@getIndex'));
        $this->assertTrue($this->client->getResponse()->isOk());
    }

    /**
     * Tests create action.
     *
     * @test
     */
    public function testGetCreate()
    {
        $crawler = $this->client->request('GET', action('GameController@getCreate'));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));
        $user = new User(array(
            'name'                  => 'admin',
            'email'                 => 'foo@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            )
        );
        $user->save();
        Auth::login($user);
        $crawler = $this->client->request('GET', action('GameController@getCreate'));
        $this->assertTrue($this->client->getResponse()->isOk());

        $game = Game::create(array('owner' => $user));
        $crawler = $this->client->request('GET', action('GameController@getCreate', $game->id));
        $this->assertTrue($this->client->getResponse()->isOk());

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $crawler = $this->client->request('GET', action('GameController@getCreate', 1138));
    }

    /**
     * Tests store.
     *
     * @test
     */
    public function testPostStore()
    {
        $crawler = $this->client->request('POST', action('GameController@postStore'), array('_token' => Session::token()));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));
        $user = new User(array(
            'name'                  => 'admin',
            'email'                 => 'foo@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            )
        );
        $user->save();
        Auth::login($user);

        $this->client->request('GET', action('GameController@getCreate')); // Populating Redirect URL.

        $crawler = $this->client->request('POST', action('GameController@postStore'), array('_token' => Session::token(), 'type' => 'asfasf'));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getCreate')));
        $this->assertSessionHasErrors();

        $crawler = $this->client->request('POST', action('GameController@postStore'), array(
            '_token'      => Session::token(),
            'name'        => 'StarBoard Test',
            'type'        => 'starboard',
            'max_players' => 4,
        ));
        $game = $user->ownGames->first();
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));
        $this->assertEquals('StarBoard Test', $game->name);
        $this->assertEquals('starboard', $game->type);
        $this->assertEquals(4, $game->maxPlayers);
        $this->assertEquals(Game::STATE_OPEN, $game->state);
    }

    /**
     * Tests show action.
     *
     * @test
     */
    public function testGetShow()
    {
        $crawler = $this->client->request('GET', action('GameController@getShow'));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));
        $user = new User(array(
            'name'                  => 'admin',
            'email'                 => 'foo@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            )
        );
        $user->save();
        Auth::login($user);
        $game = Game::create(array('owner' => $user, 'max_players' => 2));
        $crawler = $this->client->request('GET', action('GameController@getShow', $game->id));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getCreate', $game->id)));

        $game->state = Game::STATE_OPEN;
        $game->save();
        $crawler = $this->client->request('GET', action('GameController@getShow', $game->id));
        $this->assertTrue($this->client->getResponse()->isOk());
        $game = Game::find($game->id);
        $this->assertEquals($user->id, $game->players->first()->user_id);

        $user2 = User::create(array(
                'name'                  => 'user',
                'email'                 => 'user@example.com',
                'password'              => 'password',
                'password_confirmation' => 'password',
                )
        );
        $player2 = Player::create(array('user' => $user2, 'game' => $game));

        $user3 = User::create(array(
                'name'                  => 'user3',
                'email'                 => 'user3@example.com',
                'password'              => 'password',
                'password_confirmation' => 'password',
                )
        );
        Auth::login($user3);
        $crawler = $this->client->request('GET', action('GameController@getShow', $game->id));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getIndex')));
        $this->assertSessionHasErrors();
        $game = Game::find($game->id);
        $this->assertCount((int) $game->maxPlayers, $game->players);

        $game->state = 1138;
        $game->save();
        $crawler = $this->client->request('GET', action('GameController@getShow', $game->id));
        $this->assertTrue($this->client->getResponse()->isOk());

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $crawler = $this->client->request('GET', action('GameController@getShow'));
    }

    /**
     * Tests update.
     *
     * @test
     */
    public function testPutUpdate()
    {
        $crawler = $this->client->request('PUT', action('GameController@putUpdate'), array('_token' => Session::token()));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));
        $user = User::create(array(
                'name'                  => 'admin',
                'email'                 => 'foo@example.com',
                'password'              => 'password',
                'password_confirmation' => 'password',
                )
        );
        Auth::login($user);

        $game = Game::create(array('owner' => $user, 'state' => Game::STATE_OPEN, 'max_players' => 2));
        $player1 = Player::create(array('user' => $user, 'game' => $game));
        $crawler = $this->client->request('PUT', action('GameController@putUpdate', $game->id), array('_token' => Session::token(), 'state' => Game::STATE_SETUP_GALAXY));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));
        $this->assertSessionHasErrors();
        $game = Game::find($game->id);
        $this->assertEquals(Game::STATE_OPEN, $game->state);

        $user2 = User::create(array(
                'name'                  => 'user',
                'email'                 => 'user@example.com',
                'password'              => 'password',
                'password_confirmation' => 'password',
                )
        );
        $player2 = Player::create(array('user' => $user2, 'game' => $game));

        $crawler = $this->client->request('GET', action('GameController@getShow', $game->id)); // Populating Redirect URL.

        $crawler = $this->client->request('PUT', action('GameController@putUpdate', $game->id), array('_token' => Session::token(), 'state' => Game::STATE_SETUP_GALAXY));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));
        $this->assertSessionHasErrors();
        $game = Game::find($game->id);
        $this->assertEquals(Game::STATE_OPEN, $game->state);

        $player2->factionType = 'minsk';
        $player2->save();
        $user3 = User::create(array(
                'name'                  => 'user3',
                'email'                 => 'user3@example.com',
                'password'              => 'password',
                'password_confirmation' => 'password',
                )
        );
        $player3 = Player::create(array('user' => $user3, 'game' => $game, 'faction_type' => 'queen'));
        $crawler = $this->client->request('PUT', action('GameController@putUpdate', $game->id), array('_token' => Session::token(), 'state' => Game::STATE_SETUP_GALAXY));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));
        $this->assertSessionHasErrors();
        $game = Game::find($game->id);
        $this->assertEquals(Game::STATE_OPEN, $game->state);

        $player3->delete();
        $crawler = $this->client->request('PUT', action('GameController@putUpdate', $game->id), array(
            '_token' => Session::token(),
            'state'  => Game::STATE_SETUP_GALAXY,
        ));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));
        $game = Game::find($game->id);
        $this->assertEquals(Game::STATE_SETUP_GALAXY, $game->state);
        $player1 = Player::find($player1->id);
        $this->assertCount(2, $player1->planets);
        $player2 = Player::find($player2->id);
        $this->assertCount(2, $player2->planets);
        $this->assertEquals($player1->id, $game->activePlayer->id);
    }

    /**
     * Put Planet
     *
     * @test
     */
    public function testPutPlanet()
    {
        // Not logged in:
        $crawler = $this->client->request('PUT', action('GameController@putPlanet'), array('_token' => Session::token()));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));

        $user1 = User::create(array(
                'name'                  => 'admin',
                'email'                 => 'foo@example.com',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::create(array('owner' => $user1, 'state' => Game::STATE_SETUP_GALAXY, 'max_players' => 2));
        $player1 = Player::create(array('user' => $user1, 'game' => $game));
        $user2 = User::create(array(
                'name'                  => 'user',
                'email'                 => 'user@example.com',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $player2 = Player::create(array('user' => $user2, 'game' => $game));

        $game->activePlayer = $player1;
        $game->save();

        Auth::login($user1);

        // No Planet specified:
        $crawler = $this->client->request('PUT', action('GameController@putPlanet', $game->id), array('_token' => Session::token()));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));
        $this->assertSessionHasErrors();

        $planet1 = Planet::create(array('player' => $player1));
        Auth::login($user2);
        // Not the active player:
        $crawler = $this->client->request('PUT', action('GameController@putPlanet', $game->id), array('_token' => Session::token(), 'planet_id' => $planet1->id, 'planet_x_position' => 0, 'planet_y_position' => 0));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));
        $this->assertSessionHasErrors();

        $planet1 = Planet::find($planet1->id);
        $this->assertNull($planet1->xPosition);
        $this->assertNull($planet1->yPosition);
        $game = Game::find($game->id);
        $this->assertEquals($player1->id, $game->activePlayer->id);

        Auth::login($user1);
        // Invalid position:
        $crawler = $this->client->request('PUT', action('GameController@putPlanet', $game->id), array('_token' => Session::token(), 'planet_id' => $planet1->id, 'planet_x_position' => 1, 'planet_y_position' => 1));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));
        $this->assertSessionHasErrors();

        $planet1 = Planet::find($planet1->id);
        $this->assertNull($planet1->xPosition);
        $this->assertNull($planet1->yPosition);
        $game = Game::find($game->id);
        $this->assertEquals($player1->id, $game->activePlayer->id);

        // Valid, first planet is placed.
        $crawler = $this->client->request('PUT', action('GameController@putPlanet', $game->id), array('_token' => Session::token(), 'planet_id' => $planet1->id, 'planet_x_position' => 0, 'planet_y_position' => 0));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));

        $planet1 = Planet::find($planet1->id);
        $this->assertEquals(0, $planet1->xPosition);
        $this->assertEquals(0, $planet1->yPosition);
        $game = Game::find($game->id);
        $this->assertEquals($player2->id, $game->activePlayer->id);
        $this->assertEquals(Game::STATE_SETUP_GALAXY_REVERSE, $game->state);

        Auth::login($user2);
        $planet2 = Planet::create(array('player' => $player2, 'planet_type' => 'luna'));
        // Planet belongs to other user:
        $crawler = $this->client->request('PUT', action('GameController@putPlanet', $game->id), array('_token' => Session::token(), 'planet_id' => $planet1->id, 'planet_x_position' => 0, 'planet_y_position' => 1));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));
        $this->assertSessionHasErrors();

        $planet2 = Planet::find($planet2->id);
        $this->assertNull($planet2->xPosition);
        $this->assertNull($planet2->yPosition);
        $game = Game::find($game->id);
        $this->assertEquals($player2->id, $game->activePlayer->id);
        $this->assertEquals(Game::STATE_SETUP_GALAXY_REVERSE, $game->state);

        // Planet on this position already exists:
        $crawler = $this->client->request('PUT', action('GameController@putPlanet', $game->id), array('_token' => Session::token(), 'planet_id' => $planet2->id, 'planet_x_position' => 0, 'planet_y_position' => 0));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));
        $this->assertSessionHasErrors();

        $planet2 = Planet::find($planet2->id);
        $this->assertNull($planet2->xPosition);
        $this->assertNull($planet2->yPosition);
        $game = Game::find($game->id);
        $this->assertEquals($player2->id, $game->activePlayer->id);
        $this->assertEquals(Game::STATE_SETUP_GALAXY_REVERSE, $game->state);

        // Invalid position, not a neighbor of the other planet:
        $crawler = $this->client->request('PUT', action('GameController@putPlanet', $game->id), array('_token' => Session::token(), 'planet_id' => $planet2->id, 'planet_x_position' => 1, 'planet_y_position' => 1));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));
        $this->assertSessionHasErrors();

        $planet2 = Planet::find($planet2->id);
        $this->assertNull($planet2->xPosition);
        $this->assertNull($planet2->yPosition);
        $game = Game::find($game->id);
        $this->assertEquals($player2->id, $game->activePlayer->id);
        $this->assertEquals(Game::STATE_SETUP_GALAXY_REVERSE, $game->state);

        // Valid, second planet is placed:
        $crawler = $this->client->request('PUT', action('GameController@putPlanet', $game->id), array('_token' => Session::token(), 'planet_id' => $planet2->id, 'planet_x_position' => 0, 'planet_y_position' => 1));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));

        $planet2 = Planet::find($planet2->id);
        $this->assertEquals(0, $planet2->xPosition);
        $this->assertEquals(1, $planet2->yPosition);
        $game = Game::find($game->id);
        $this->assertEquals($player1->id, $game->activePlayer->id);
        $this->assertEquals(Game::STATE_SETUP_GALAXY, $game->state);

        $navigationRoute = NavigationRoute::firstOrNew(array(
                'planet1_id' => $planet1->id,
                'planet2_id' => $planet2->id
        ));
        $this->assertNotNull($navigationRoute->id);

        Auth::login($user1);
        $planet3 = Planet::create(array('player' => $player1, 'planet_type' => 'venus'));
        // Try to place third planet to the right of vertically aligned planet with two axes:
        $crawler = $this->client->request('PUT', action('GameController@putPlanet', $game->id), array('_token' => Session::token(), 'planet_id' => $planet3->id, 'planet_x_position' => 1, 'planet_y_position' => 1));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));
        $this->assertSessionHasErrors();

        $planet3 = Planet::find($planet3->id);
        $this->assertNull($planet3->xPosition);
        $this->assertNull($planet3->yPosition);
        $game = Game::find($game->id);
        $this->assertEquals($player1->id, $game->activePlayer->id);
        $this->assertEquals(Game::STATE_SETUP_GALAXY, $game->state);

        // Valid, third planet is placed:
        $crawler = $this->client->request('PUT', action('GameController@putPlanet', $game->id), array('_token' => Session::token(), 'planet_id' => $planet3->id, 'planet_x_position' => 0, 'planet_y_position' => 2));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));

        $planet3 = Planet::find($planet3->id);
        $this->assertEquals(0, $planet3->xPosition);
        $this->assertEquals(2, $planet3->yPosition);
        $game = Game::find($game->id);
        $this->assertEquals($player2->id, $game->activePlayer->id);
        $this->assertEquals(Game::STATE_SETUP_GALAXY_REVERSE, $game->state);

        $navigationRoute = NavigationRoute::firstOrNew(array(
                'planet1_id' => $planet2->id,
                'planet2_id' => $planet3->id
        ));
        $this->assertNotNull($navigationRoute->id);

        // Add planets to the right and below planet1.
        $planet4 = Planet::create(array('player' => $player1, 'x_position' => 1, 'y_position' => 0));
        $planet5 = Planet::create(array('player' => $player1, 'x_position' => 0, 'y_position' => -1));
        NavigationRoute::create(array('planet1_id' => $planet1->id, 'planet2_id' => $planet4->id));
        NavigationRoute::create(array('planet1_id' => $planet1->id, 'planet2_id' => $planet5->id));

        $planet6 = Planet::create(array('player' => $player2));
        Auth::login($user2);
        // Valid 6th planet is placed with connections to 4 and 5.
        $crawler = $this->client->request('PUT', action('GameController@putPlanet', $game->id), array('_token' => Session::token(), 'planet_id' => $planet6->id, 'planet_x_position' => 1, 'planet_y_position' => -1));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));

        $planet6 = Planet::find($planet6->id);
        $this->assertEquals(1, $planet6->xPosition);
        $this->assertEquals(-1, $planet6->yPosition);
        $game = Game::find($game->id);
        $this->assertEquals($player1->id, $game->activePlayer->id);
        $this->assertEquals(Game::STATE_SETUP_GALAXY, $game->state);

        $navigationRoute = NavigationRoute::firstOrNew(array(
                'planet1_id' => $planet4->id,
                'planet2_id' => $planet6->id
        ));
        $this->assertNotNull($navigationRoute->id);
        $navigationRoute = NavigationRoute::firstOrNew(array(
                'planet1_id' => $planet5->id,
                'planet2_id' => $planet6->id
        ));
        $this->assertNotNull($navigationRoute->id);

        // Add Planet with three routes above planet2.
        $planet7 = Planet::create(array('player' => $player2, 'planet_type' => 'mars', 'x_position' => 0, 'y_position' => 2));

        $planet8 = Planet::create(array('player' => $player1));
        Auth::login($user1);
        // Try to add 8th planet above 7th
        $crawler = $this->client->request('PUT', action('GameController@putPlanet', $game->id), array('_token' => Session::token(), 'planet_id' => $planet8->id, 'planet_x_position' => 0, 'planet_y_position' => 3));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));
        $this->assertSessionHasErrors();

        $planet8 = Planet::find($planet8->id);
        $this->assertNull($planet8->xPosition);
        $this->assertNull($planet8->yPosition);
        $game = Game::find($game->id);
        $this->assertEquals($player1->id, $game->activePlayer->id);
        $this->assertEquals(Game::STATE_SETUP_GALAXY, $game->state);

        // Successfully add 8th planet to the left of the 7th
        $crawler = $this->client->request('PUT', action('GameController@putPlanet', $game->id), array('_token' => Session::token(), 'planet_id' => $planet8->id, 'planet_x_position' => -1, 'planet_y_position' => 2));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));

        $planet8 = Planet::find($planet8->id);
        $this->assertEquals(-1, $planet8->xPosition);
        $this->assertEquals(2, $planet8->yPosition);
        $game = Game::find($game->id);
        $this->assertEquals($player2->id, $game->activePlayer->id);
        $this->assertEquals(Game::STATE_SETUP_GALAXY_REVERSE, $game->state);

        // New Game to easily setup new test.
        $game = Game::create(array('owner' => $user1, 'state' => Game::STATE_SETUP_GALAXY));
        $player1 = Player::create(array('user' => $user1, 'game' => $game));
        $player2 = Player::create(array('user' => $user2, 'game' => $game));
        $game->activePlayer = $game->nextPlayer;
        $game->save();
        $planet1 = Planet::create(array('player' => $player2, 'x_position' => 0, 'y_position' => 0));
        $planet2 = Planet::create(array('player' => $player2, 'x_position' => 0, 'y_position' => 1, 'planet_type' => 'mars')); // Above of planet1, three routes
        NavigationRoute::create(array('planet1' => $planet1, 'planet2' => $planet2));
        $planet3 = Planet::create(array('player' => $player2, 'x_position' => -1, 'y_position' => 1, 'planet_type' => 'pluto')); // To left of planet2, two routes
        NavigationRoute::create(array('planet1' => $planet2, 'planet2' => $planet3));

        $planet4 = Planet::create(array('player' => $player1));
        // Add planet4 to the left of planet1
        $crawler = $this->client->request('PUT', action('GameController@putPlanet', $game->id), array('_token' => Session::token(), 'planet_id' => $planet4->id, 'planet_x_position' => -1, 'planet_y_position' => 0));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));

        $planet4 = Planet::find($planet4->id);
        $this->assertEquals(-1, $planet4->xPosition);
        $this->assertEquals(0, $planet4->yPosition);
        $game = Game::find($game->id);
        $this->assertEquals($player2->id, $game->activePlayer->id);
        $this->assertEquals(Game::STATE_SETUP_GALAXY_REVERSE, $game->state);

        $navigationRoute = NavigationRoute::firstOrNew(array('planet1_id' => $planet1->id, 'planet2_id' => $planet4->id));
        $this->assertNotNull($navigationRoute->id);
        $navigationRoute = NavigationRoute::firstOrNew(array('planet1_id' => $planet3->id, 'planet2_id' => $planet4->id));
        $this->assertNull($navigationRoute->id);


        // Game does not exist:
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $crawler = $this->client->request('PUT', action('GameController@putPlanet', $game->id + 1), array('_token' => Session::token(), 'planet_id' => $planet2->id, 'planet_x_position' => 1, 'planet_y_position' => 0));
    }

}
