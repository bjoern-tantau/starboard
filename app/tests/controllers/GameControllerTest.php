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
        $game = Game::create(array('owner' => $user));
        $crawler = $this->client->request('GET', action('GameController@getShow', $game->id));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getCreate', $game->id)));

        $game->state = Game::STATE_OPEN;
        $game->save();
        $crawler = $this->client->request('GET', action('GameController@getShow', $game->id));
        $this->assertTrue($this->client->getResponse()->isOk());

        $game->state = 1138;
        $game->save();
        $crawler = $this->client->request('GET', action('GameController@getShow', $game->id));
        $this->assertTrue($this->client->getResponse()->isOk());

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $crawler = $this->client->request('GET', action('GameController@getShow'));
    }

}
