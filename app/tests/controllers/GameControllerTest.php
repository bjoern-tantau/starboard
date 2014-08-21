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
    }

}
