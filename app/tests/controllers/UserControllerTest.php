<?php

class Controllers_UserControllerTest extends TestCase
{

    /**
     * Tests index.
     *
     * @test
     */
    public function testIndex()
    {
        Route::enableFilters();

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

        $this->assertFalse(Auth::check());
        $crawler = $this->client->request('GET', route('user.index'));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));

        Auth::login($user);

        $crawler = $this->client->request('GET', route('user.index'));
        $this->assertTrue($this->client->getResponse()->isOk());
    }

    /**
     * Tests create.
     *
     * @test
     */
    public function testCreate()
    {
        Route::enableFilters();

        $crawler = $this->client->request('GET', route('user.create'));
        $this->assertTrue($this->client->getResponse()->isOk());
    }

    /**
     * Tests store.
     *
     * @test
     */
    public function testStore()
    {
        Route::enableFilters();

        $this->client->request('GET', url('/'));

        $crawler = $this->client->request('POST', route('user.store'), array('_token' => Session::token()));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('/')));
        $this->assertSessionHasErrors();

        $crawler = $this->client->request('POST', route('user.store'), array(
            '_token'                => Session::token(),
            'name'                  => 'admin',
            'email'                 => 'foo@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ));
        $this->assertTrue($this->client->getResponse()->isRedirect(route('user.show', User::all()->first()->id)));
    }

    /**
     * Tests show.
     *
     * @test
     */
    public function testShow()
    {
        Route::enableFilters();

        $user = new User(array(
            'name'                  => 'admin',
            'email'                 => 'foo@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            )
        );
        $user->save();

        $this->assertFalse(Auth::check());
        $crawler = $this->client->request('GET', route('user.show'));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));

        Auth::login($user);

        $crawler = $this->client->request('GET', route('user.show', $user->id));
        $this->assertTrue($this->client->getResponse()->isOk());

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $crawler = $this->client->request('GET', route('user.show', $user->id + 10));
    }

    /**
     * Tests edit.
     *
     * @test
     */
    public function testEdit()
    {
        Route::enableFilters();

        $user = new User(array(
            'name'                  => 'user',
            'email'                 => 'user@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            )
        );
        $user->save();
        $firstUser = $user;

        $this->assertFalse(Auth::check());
        $crawler = $this->client->request('GET', route('user.edit', $user->id));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));

        Auth::login($user);

        $crawler = $this->client->request('GET', route('user.edit', $user->id));
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

        $crawler = $this->client->request('GET', route('user.edit', $user->id));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));

        Auth::login($user);
        $crawler = $this->client->request('GET', route('user.edit', $firstUser->id));
        $this->assertTrue($this->client->getResponse()->isOk());

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $crawler = $this->client->request('GET', route('user.edit', $user->id + 10));
    }

    /**
     * Tests update.
     *
     * @test
     */
    public function testUpdate()
    {
        Route::enableFilters();

        $user = new User(array(
            'name'                  => 'user',
            'email'                 => 'user@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            )
        );
        $user->save();
        $firstUser = $user;

        $this->assertFalse(Auth::check());
        $crawler = $this->client->request('PUT', route('user.update', $user->id));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));

        Auth::login($user);

        $crawler = $this->client->request('PUT', route('user.update', $user->id));
        $this->assertTrue($this->client->getResponse()->isRedirect(route('user.edit', $user->id)));
        $this->assertSessionHasErrors();

        $crawler = $this->client->request('PUT', route('user.update', $user->id), array(
            'name'  => 'user',
            'email' => 'user@example.com',
        ));
        $this->assertTrue($this->client->getResponse()->isRedirect(route('user.show', $user->id)));

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $crawler = $this->client->request('PUT', route('user.update', $user->id + 10));
    }

    /**
     * Tests destroy.
     *
     * @test
     */
    public function testDestroy()
    {
        Route::enableFilters();

        $this->client->request('GET', url('/'));

        $user = new User(array(
            'name'                  => 'user',
            'email'                 => 'user@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            )
        );
        $user->save();
        $firstUser = $user;

        $this->assertFalse(Auth::check());
        $crawler = $this->client->request('DELETE', route('user.destroy', $user->id));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('login')));

        Auth::login($user);

        $this->client->request('GET', url('/'));

        $crawler = $this->client->request('DELETE', route('user.destroy', $user->id));
        $this->assertTrue($this->client->getResponse()->isRedirect(url('/')));

        $this->assertNull(User::find($user->id));

        Route::disableFilters();
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $crawler = $this->client->request('DELETE', route('user.destroy', $user->id + 10));
    }

}
