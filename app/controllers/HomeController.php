<?php

class HomeController extends BaseController
{

    /**
     * Redirect to config or login.
     *
     * @return Response
     */
    public function getIndex()
    {
        if (!Auth::check()) {
            if (!User::admins()->count()) {
                return Redirect::action('ConfigController@getIndex');
            }
            return Redirect::guest('login');
        }
        $this->layout->content = View::make('index');
        return;
    }

}
