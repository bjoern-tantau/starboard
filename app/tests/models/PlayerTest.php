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
            'character_type' => 'jack',
            'active'         => true,
        );

        $game = Game::firstOrCreate(array('owner' => $user));
        $player = Player::firstOrCreate(array('user' => $user, 'game' => $game));

        $this->assertEquals($expected['character_type'], $player->characterType);
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
        $game = Game::firstOrCreate(array('owner' => $user));
        $data = array(
            'user'           => $user,
            'game'           => $game,
            'character_type' => 'minsk',
            'active'         => 1,
        );
        $player = Player::firstOrCreate($data);

        $loaded = Player::find($player->id);
        $this->assertEquals($data['character_type'], $loaded->characterType);
        $this->assertEquals($data['active'], $loaded->active);
        $this->assertEquals($data['user']->id, $loaded->user->id);
        $this->assertEquals($data['game']->id, $loaded->game->id);
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
            'The specified character type is not available.',
        );
        $this->assertEquals($expected, $errors);

        unset($player->game);

        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::firstOrCreate(array('owner' => $user));
        $player->user = $user;
        $player->game = $game;
        $player->characterType = 'qawfasf';

        $this->assertFalse($player->save());
        $errors = $player->errors()->all();
        $this->assertCount(1, $errors);
        $expected = array(
            'The specified character type is not available.',
        );
        $this->assertEquals($expected, $errors);

        $player->characterType = 'jack';

        $this->assertTrue($player->save());
        $errors = $player->errors()->all();
        $this->assertCount(0, $errors);
    }

    /**
     * Test that character XML Data is available and changing with the character name.
     *
     * @test
     */
    public function testCharacterData()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $game = Game::firstOrCreate(array('owner' => $user));
        $player = Player::firstOrCreate(array('user' => $user, 'game' => $game));

        $this->assertInstanceOf('SimpleXMLElement', $player->character);
        $this->assertEquals('Jack Reynolds', $player->character->name);
        $this->assertEquals('humans', $player->character->race);
        $this->assertEquals('blue', $player->character->color);

        $player->characterType = 'queen';
        $this->assertEquals('The Queen of Knives', $player->character->name);
        $this->assertEquals('aliens', $player->character->race);
        $this->assertEquals('purple', $player->character->color);
    }

}
