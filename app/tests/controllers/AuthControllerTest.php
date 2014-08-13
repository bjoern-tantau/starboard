<?php

class Controllers_AuthControllerTest extends TestCase
{

    /**
     * Tests index.
     *
     * @test
     */
    public function testGetIndex()
    {
        $crawler = $this->client->request('GET', action('AuthController@getIndex'));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('AuthController@getLogin')));
    }

    /**
     * Tests login.
     *
     * @test
     */
    public function testGetLogin()
    {
        $crawler = $this->client->request('GET', action('AuthController@getLogin'));
        $this->assertTrue($this->client->getResponse()->isOk());
    }

    /**
     * Tests login.
     *
     * @test
     */
    public function testPostLogin()
    {
        $crawler = $this->client->request('POST', action('AuthController@postLogin'));
        $this->assertTrue($this->client->getResponse()->isRedirect(action('AuthController@getLogin')));
        $this->assertSessionHasErrors();
        $this->assertFalse(Auth::check());

        $user = new User(array(
            'name'                  => 'admin',
            'email'                 => 'foo@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            )
        );
        $user->save();

        $crawler = $this->client->request('POST', action('AuthController@postLogin'), array(
            'email'    => 'foo@example.com',
            'password' => 'password',
        ));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('/')));
        $this->assertTrue(Auth::check());
        $this->assertTrue(Auth::user()->id == $user->id);
    }

    /**
     * Tests logout.
     *
     * @test
     */
    public function testGetLogout()
    {
        $user = new User(array(
            'name'                  => 'admin',
            'email'                 => 'foo@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            )
        );
        $user->save();
        Auth::login($user);
        $crawler = $this->client->request('GET', action('AuthController@getLogout'));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));
        $this->assertFalse(Auth::check());
    }

    /**
     * Tests reminder.
     *
     * @test
     */
    public function testGetRemind()
    {
        $crawler = $this->client->request('GET', action('AuthController@getRemind'));
        $this->assertTrue($this->client->getResponse()->isOk());
    }

    /**
     * Tests reminder.
     *
     * @test
     */
    public function testPostRemind()
    {
        $crawler = $this->client->request('POST', action('AuthController@postRemind'));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));
        $this->assertSessionHasErrors();

        $user = new User(array(
            'name'                  => 'admin',
            'email'                 => 'foo@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            )
        );
        $user->save();

        $crawler = $this->client->request('POST', action('AuthController@postRemind'), array('email' => 'foo@example.com'));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));
        $this->assertSessionHas('message');
    }

    /**
     * Tests resetter.
     *
     * @test
     */
    public function testGetReset()
    {
        $crawler = $this->client->request('GET', action('AuthController@getReset', array('foobar')));
        $this->assertTrue($this->client->getResponse()->isOk());

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $crawler = $this->client->request('GET', action('AuthController@getReset'));
    }

    /**
     * Tests resetter.
     *
     * @test
     */
    public function testPostReset()
    {
        $this->client->request('GET', url('/'));

        $crawler = $this->client->request('POST', action('AuthController@postReset'));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('/')));
        $this->assertSessionHasErrors();

        $user = new User(array(
            'name'                  => 'admin',
            'email'                 => 'foo@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ));
        $user->save();
        $repository = new \Illuminate\Auth\Reminders\DatabaseReminderRepository(DB::connection(), 'password_reminders', 'token');
        $token = $repository->create($user);

        $crawler = $this->client->request('POST', action('AuthController@postReset'), array(
            'token'                 => $token,
            'email'                 => 'foo@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));
    }

}
