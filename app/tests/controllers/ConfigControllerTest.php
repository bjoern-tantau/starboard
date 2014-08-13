<?php

class Controllers_ConfigControllerTest extends TestCase
{

    /**
     * Tests index.
     *
     * @test
     */
    public function testGetIndex()
    {
        $crawler = $this->client->request('GET', action('ConfigController@getIndex'));
        $this->assertTrue($this->client->getResponse()->isOk());

        $user = new User(array(
            'name'                  => 'admin',
            'email'                 => 'foo@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            )
        );
        $user->save();
        $group = Group::firstOrCreate(array(
                'name'     => 'admin',
                'is_admin' => true,
        ));
        $user->groups()->save($group);

        $crawler = $this->client->request('GET', action('ConfigController@getIndex'));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));
    }

    /**
     * Tests postAdmin.
     *
     * @test
     */
    public function testPostAdmin()
    {
        $crawler = $this->client->request('POST', action('ConfigController@postCreateAdmin'));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('ConfigController@getIndex')));
        $this->assertSessionHasErrors();

        $crawler = $this->client->request('POST', action('ConfigController@postCreateAdmin'), array(
            'name'                  => 'admin',
            'email'                 => 'foo@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('/')));
    }

}
