<?php

class UserController extends \BaseController
{

    /**
     * Instantiate a new UserController instance.
     */
    public function __construct()
    {
        $this->beforeFilter('auth', array('except' => array('create', 'store')));
        $this->beforeFilter('csrf', array('on' => 'post'));
        $this->beforeFilter('admin', array('only' => array('index')));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $this->layout
            ->content = View::make('user.index', array(
                'users' => User::all(),
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->layout
            ->content = View::make('user.edit', array(
                'user' => new User(),
        ));
        return;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $user = new User(Input::all());
        if ($user->save()) {
            return Redirect::route('user.show', $user->id)->with('message', 'User created.');
        } else {
            return Redirect::back()->withErrors($user->errors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return App::abort(404);
        }
        $authUser = Auth::user();
        $canEdit = ($authUser->id == $user->id || $authUser->isAdmin());
        $this->layout->content = View::make('user.show', array(
                'user'    => $user,
                'canEdit' => $canEdit,
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        if (!$user) {
            return App::abort(404);
        }
        $authUser = Auth::user();
        $canEdit = ($authUser->id == $user->id || $authUser->isAdmin());
        if ($canEdit) {
            $this->layout->content = View::make('user.edit', array(
                    'user' => $user,
            ));
        } else {
            return Redirect::guest('login');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $user = User::find($id);
        if (!$user) {
            return App::abort(404);
        }
        $authUser = Auth::user();
        $canEdit = ($authUser->id == $user->id || $authUser->isAdmin());
        if ($canEdit) {
            $user->fill(Input::only(array(
                    'name', 'email',
            )));
            if (Input::has('password')) {
                $user->fill(Input::only(array(
                        'password', 'password_confirmation',
                )));
            }
            if ($user->save()) {
                return Redirect::route('user.show', $user->id)->with('message', 'User saved.');
            } else {
                return Redirect::route('user.edit', $user->id)->withErrors($user->errors());
            }
        } else {
            return Redirect::guest('login');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return App::abort(404);
        }
        $authUser = Auth::user();
        $canEdit = ($authUser->id == $user->id || $authUser->isAdmin());

        if ($canEdit) {
            $user->delete();
            Auth::logout();
            return Redirect::back();
        } else {
            return Redirect::guest('login');
        }
    }

}
