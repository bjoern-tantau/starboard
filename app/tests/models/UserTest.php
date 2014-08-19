<?php

class Models_UserTest extends TestCase
{

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        User::boot();
    }

    /**
     * Are we using Ardent?
     *
     * @test
     */
    public function testUsingArdent()
    {
        $user = new User();
        $this->assertInstanceOf('LaravelBook\Ardent\Ardent', $user);
    }

    /**
     * Test that all fields of a user are required.
     *
     * @test
     */
    public function testFieldsRequired()
    {
        $user = new User();
        $this->assertFalse($user->save());

        $errors = $user->errors()->all();
        $this->assertCount(3, $errors);

        $expected = array(
            'The email field is required.',
            'The name field is required.',
            'The password field is required.',
        );
        $this->assertEquals($expected, $errors);

        $user->email = 'foobar';
        $user->name = 'name#+ü';
        $user->password = 'pass';
        $this->assertFalse($user->save());

        $errors = $user->errors()->all();
        $this->assertCount(4, $errors);

        $expected = array(
            'The email must be a valid email address.',
            'The name may only contain letters, numbers, and dashes.',
            'The password must be at least 6 characters.',
            'The password confirmation does not match.',
        );
        $this->assertEquals($expected, $errors);

        $user->email = 'foo@exampl.com';
        $user->name = 'name';
        $user->password = 'password';
        $user->password_confirmation = 'password';

        $this->assertTrue($user->save());

        $user = new User();
        $user->email = 'foo@exampl.com';
        $user->name = 'name';
        $user->password = 'password';
        $user->password_confirmation = 'password';

        $this->assertFalse($user->save());
        $errors = $user->errors()->all();
        $this->assertCount(2, $errors);

        $expected = array(
            'The email has already been taken.',
            'The name has already been taken.',
        );
        $this->assertEquals($expected, $errors);
    }

    /**
     * Test Group associations.
     *
     * @test
     */
    public function testGroupAssocications()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $this->assertNotNull($user->id);
        $this->assertCount(0, $user->groups);
        $adminGroup = Group::firstOrCreate(array('name' => 'admin', 'is_admin' => true));
        $user->groups()->save($adminGroup);
        $user = User::find($user->id);
        $this->assertCount(1, $user->groups);
        $group = $user->groups()->first();
        $this->assertInstanceOf('Group', $group);
        $this->assertEquals($group->id, $adminGroup->id);
    }

    /**
     * Test isAdmin attribute.
     *
     * @test
     */
    public function testIsAdminAttribute()
    {
        $user = new User();
        $user->email = 'admin@exampl.com';
        $user->name = 'admin';
        $user->password = 'password';
        $user->password_confirmation = 'password';
        $user->save();

        $this->assertFalse($user->isAdmin);

        $adminGroup = Group::firstOrCreate(array('name' => 'admin', 'is_admin' => true));

        $user->groups()->save($adminGroup);
        $user = User::find($user->id);

        $this->assertTrue($user->isAdmin);
    }

    /**
     * Test Admin scope.
     *
     * @test
     */
    public function testAdminScope()
    {
        $user = new User();
        $user->email = 'foo@exampl.com';
        $user->name = 'name';
        $user->password = 'password';
        $user->password_confirmation = 'password';
        $user->save();

        $user = new User();
        $user->email = 'admin@exampl.com';
        $user->name = 'admin';
        $user->password = 'password';
        $user->password_confirmation = 'password';
        $user->save();

        $adminGroup = Group::firstOrCreate(array('name' => 'admin', 'is_admin' => true));

        $user->groups()->save($adminGroup);

        $adminUsers = User::admins()->get();
        $this->assertEquals(1, $adminUsers->count());

        $this->assertEquals($user->id, $adminUsers->first()->id);
    }

    /**
     * Test Group Scope
     *
     * @test
     */
    public function testGroupScope()
    {

        $user = new User();
        $user->email = 'admin@exampl.com';
        $user->name = 'admin';
        $user->password = 'password';
        $user->password_confirmation = 'password';
        $user->save();

        $group = Group::firstOrNew(array('name' => 'admin', 'is_admin' => true));
        $user->groups()->save($group);

        $groups = array(
            new Group(array('name' => 'test')),
            $group,
        );

        $user = new User();
        $user->email = 'foo@exampl.com';
        $user->name = 'name';
        $user->password = 'password';
        $user->password_confirmation = 'password';
        $user->save();
        $user->groups()->saveMany($groups);

        $adminUsers = User::group('admin')->get();
        $this->assertEquals(2, $adminUsers->count());

        $testUsers = User::group('test')->get();
        $this->assertEquals(1, $testUsers->count());

        $allUsers = User::group(array('admin', 'test'))->get();
        $this->assertEquals(2, $allUsers->count());
    }

    /**
     * Is password hashed?
     *
     * @test
     */
    public function testIsPasswordHashed()
    {
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));
        $this->assertNotEquals('password', $user->password);
    }

    /**
     * Is the user associated to games?
     *
     * @test
     */
    public function testGamesAssociation()
    {
        Game::boot();
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));

        $game = Game::create(array('owner_id' => $user->id));

        $this->assertCount(0, $user->games);

        $player = Player::create(array('user_id' => $user->id, 'game_id' => $game->id));

        $user = User::find($user->id);
        $this->assertCount(1, $user->games);
        $this->assertEquals($game->id, $user->games->first()->id);
    }

    /**
     * Is the user associated to games he owns?
     *
     * @test
     */
    public function testOwnGamesAssociation()
    {
        Game::boot();
        $user = User::firstOrCreate(array(
                'email'                 => 'foo@bar.com',
                'name'                  => 'foobar',
                'password'              => 'password',
                'password_confirmation' => 'password',
        ));

        $this->assertCount(0, $user->ownGames);

        $game = new Game();
        $user->ownGames()->save($game);

        $user = User::find($user->id);
        $this->assertCount(1, $user->ownGames);
        $this->assertEquals($game->id, $user->ownGames->first()->id);
    }

}
