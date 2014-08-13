<?php

class Controllers_HomeControllerTest extends TestCase
{

    /**
     * Tests index.
     *
     * @test
     */
    public function testGetIndex()
    {
        $crawler = $this->client->request('GET', '/');
        $this->assertTrue($this->client->getResponse()->isRedirect(action('ConfigController@getIndex')));
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

        $crawler = $this->client->request('GET', '/');
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));

        Auth::login($user);
        $crawler = $this->client->request('GET', '/');
        $this->assertTrue($this->client->getResponse()->isOk());
    }
}