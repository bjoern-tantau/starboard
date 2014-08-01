<?php

class ConfigController extends BaseController
{

    /**
     * Redirect to config or login.
     *
     * @return Response
     */
    public function getIndex()
    {
        if (!User::admins()->count()) {
            $user = new User();
            $edit = View::make('user.edit', array(
                    'user'   => $user,
                    'action' => 'ConfigController@postCreateAdmin',
            ));
            $this->layout
                ->with('globalErrors', false)
                ->content = View::make('config.index', array('userEdit' => $edit));
            return;
        }
        return Redirect::guest('login')->with('message', 'Please login.');
    }

    /**
     * Create an admin user and redirect to homepage.
     *
     * @return Response
     */
    public function postCreateAdmin()
    {
        $user = new User(Input::all());
        if ($user->save()) {
            $group = Group::firstOrCreate(array(
                    'name'     => 'admin',
                    'is_admin' => true,
            ));
            $user->groups()->save($group);
            return Redirect::to('/')->with('message', 'User created.');
        } else {
            return Redirect::action('ConfigController@getIndex')->withErrors($user->errors());
        }
    }

}
