<?php

class Controllers_PlayerControllerTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        Route::enableFilters();
        Game::boot();
    }

    /**
     * Tests update.
     *
     * @test
     */
    public function testUpdate()
    {
        $user = new User(array(
            'name'                  => 'user',
            'email'                 => 'user@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            )
        );
        $user->save();

        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        $player = Player::firstOrCreate(array('user_id' => $user->id, 'game_id' => $game->id));

        $this->assertFalse(Auth::check());
        $crawler = $this->client->request('PUT', route('player.update', $player->id));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));

        Auth::login($user);

        $crawler = $this->client->request('GET', action('GameController@getShow', $game->id));
        $crawler = $this->client->request('PUT', route('player.update', $player->id), array('faction_type' => 'foobar'));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));
        $this->assertSessionHasErrors();

        $crawler = $this->client->request('GET', action('GameController@getShow', $game->id));
        $crawler = $this->client->request('PUT', route('player.update', $player->id), array('faction_type' => 'foobar'), array(), array('HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));
        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertJson($this->client->getResponse()->getContent());
        $expected = (object) array(
                'type'     => 'error',
                'messages' => (object) array(
                    'faction_type' => array('The specified faction type is not available.'),
                ),
                'objects'  => (object) array(
                    'player' => (object) array(
                        'id'           => (string) $player->id,
                        'user_id'      => (string) $user->id,
                        'game_id'      => (string) $game->id,
                        'faction_type' => 'jack',
                        'active'       => '1',
                        'updated_at'   => $player->updatedAt,
                        'created_at'   => $player->createdAt,
                    ),
                ),
        );
        $actual = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($expected, $actual);

        $crawler = $this->client->request('PUT', route('player.update', $user->id), array(
            'faction_type' => 'minsk',
            ), array(), array('HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));
        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertJson($this->client->getResponse()->getContent());
        $player->faction_type = 'minsk';
        $expected = (object) array(
                'type'     => 'success',
                'messages' => array(
                    'Player saved.',
                ),
                'objects'  => (object) array(
                    'player' => (object) array(
                        'id'           => (string) $player->id,
                        'user_id'      => (string) $user->id,
                        'game_id'      => (string) $game->id,
                        'faction_type' => 'minsk',
                        'active'       => '1',
                        'updated_at'   => (string) $player->updatedAt,
                        'created_at'   => (string) $player->createdAt,
                        'game'         => (object) array(
                            'state'            => (string) $game->state,
                            'type'             => $game->type,
                            'name'             => (string) $game->name,
                            'max_players'      => (string) $game->maxPlayers,
                            'owner_id'         => (string) $game->ownerId,
                            'updated_at'       => (string) $game->updated_at,
                            'created_at'       => (string) $game->created_at,
                            'id'               => (string) $game->id,
                            'active_player_id' => $game->activePlayerId,
                        ),
                        'faction'      => (object) (array) $player->faction,
                    ),
                ),
        );
        $expected = json_decode(json_encode($expected));
        $actual = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($expected, $actual);


        $crawler = $this->client->request('GET', action('GameController@getShow', $game->id));
        $crawler = $this->client->request('PUT', route('player.update', $user->id), array(
            'faction_type' => 'minsk',
        ));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $crawler = $this->client->request('PUT', route('player.update', $player->id + 10));
    }

    /**
     * Tests destroy.
     *
     * @test
     */
    public function testDestroy()
    {

        $user = new User(array(
            'name'                  => 'user',
            'email'                 => 'user@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            )
        );
        $user->save();
        $game = Game::firstOrCreate(array('owner_id' => $user->id));
        $player = Player::firstOrCreate(array('user_id' => $user->id, 'game_id' => $game->id));

        $this->assertFalse(Auth::check());
        $crawler = $this->client->request('DELETE', route('player.destroy', $player->id));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));

        Auth::login($user);

        $crawler = $this->client->request('GET', action('GameController@getShow', $game->id));
        $crawler = $this->client->request('DELETE', route('player.destroy', $player->id));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('GameController@getShow', $game->id)));

        $this->assertNull(Player::find($player->id));

        $player = Player::firstOrCreate(array('user_id' => $user->id, 'game_id' => $game->id));
        $crawler = $this->client->request('GET', action('GameController@getShow', $game->id));
        $crawler = $this->client->request('DELETE', route('player.destroy', $player->id), array(), array(), array('HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));
        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertJson($this->client->getResponse()->getContent());
        $expected = (object) array(
                'type'     => 'success',
                'messages' => array(
                    'Deleted player successfully.',
                ),
        );
        $actual = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals($expected, $actual);

        $this->assertNull(Player::find($player->id));

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $crawler = $this->client->request('DELETE', route('player.destroy', $player->id + 10));
    }

}
