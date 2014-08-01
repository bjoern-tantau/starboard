<?php

class Models_GroupTest extends TestCase
{

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        Group::boot();
    }

    /**
     * Are we using Ardent?
     *
     * @test
     */
    public function testUsingArdent()
    {
        $group = new Group();
        $this->assertInstanceOf('LaravelBook\Ardent\Ardent', $group);
    }

    /**
     * Test that all fields of a user are required.
     *
     * @test
     */
    public function testFieldsRequired()
    {
        $group = new Group();
        $this->assertFalse($group->save());

        $errors = $group->errors()->all();
        $this->assertCount(1, $errors);

        $expected = array(
            'The name field is required.',
        );
        $this->assertEquals($expected, $errors);

        $group->name = 'name#+ü';

        $this->assertFalse($group->save());

        $errors = $group->errors()->all();
        $this->assertCount(1, $errors);

        $expected = array(
            'The name may only contain letters, numbers, and dashes.',
        );
        $this->assertEquals($expected, $errors);

        $group->name = 'name';

        $this->assertTrue($group->save());

        $group = new Group();
        $group->name = 'name';

        $this->assertFalse($group->save());
        $errors = $group->errors()->all();
        $this->assertCount(1, $errors);

        $expected = array(
            'The name has already been taken.',
        );
        $this->assertEquals($expected, $errors);
    }

    /**
     * Test User associations.
     *
     * @test
     */
    public function testUserAssocications()
    {
        $group = Group::firstOrCreate(array('name' => 'admin', 'is_admin' => true));

        $this->assertCount(0, $group->users);

        $user = User::firstOrCreate(array(
                'email'    => 'foo@bar.com',
                'name'     => 'foobar',
                'password' => 'password'
        ));

        $group->users()->save($user);
        $group = Group::find($group->id);
        $this->assertCount(1, $group->users);
        $user = $group->users()->first();
        $this->assertInstanceOf('User', $user);
    }

}
