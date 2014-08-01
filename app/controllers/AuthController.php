<?php

class AuthController extends BaseController
{

    /**
     * Execute the remind action per default.
     *
     * @return Response
     */
    public function getIndex()
    {
        return Redirect::action('AuthController@getLogin');
    }

    /**
     * Show the Login Form.
     *
     * @return void
     */
    public function getLogin()
    {
        $this->layout->content = View::make('auth.login');
        return;
    }

    /**
     * Login the User and return him to root.
     *
     * @return Response
     */
    public function postLogin()
    {
        $credentials = array_merge(Input::only(array('email', 'password')), array('active' => 1));
        if (Auth::attempt($credentials)) {
            return Redirect::intended('/')->with('message', 'Login Successfull!');
        }
        return Redirect::to('login')->withErrors(array('password' => 'Password invalid'))->withInput(Input::except('password'));
    }

    /**
     * Log the user out and return him to login.
     *
     * @return Response
     */
    public function getLogout()
    {
        Auth::logout();
        return Redirect::to('login')->with('message', 'Logged out successfully.');
    }

    /**
     * Display the password reminder view.
     *
     * @return Response
     */
    public function getRemind()
    {
        $this->layout->content = View::make('auth.remind');
        return $this->layout;
    }

    /**
     * Handle a POST request to remind a user of their password.
     *
     * @return Response
     */
    public function postRemind()
    {
        switch ($response = Password::remind(Input::only('email'))) {
            case Password::INVALID_USER:
                return Redirect::to('login')->with('error', Lang::get($response));

            case Password::REMINDER_SENT:
                return Redirect::to('login')->with('message', Lang::get($response));
        }
    }

    /**
     * Display the password reset view for the given token.
     *
     * @param  string  $token
     * @return Response
     */
    public function getReset($token = null)
    {
        if (is_null($token))
            App::abort(404);

        $this->layout->content = View::make('auth.reset')->with('token', $token);
        return $this->layout;
    }

    /**
     * Handle a POST request to reset a user's password.
     *
     * @return Response
     */
    public function postReset()
    {
        $credentials = Input::only(
                'email', 'password', 'password_confirmation', 'token'
        );

        try {
            $response = Password::reset($credentials, function($user, $password) {
                    /* @var $user LaravelBook\Ardent\Ardent */
                    $user->password = $password;
                    $user->password_confirmation = $password;

                    if (!$user->save()) {
                        throw new ValidateException($user->errors());
                    }
                });
        } catch (ValidateException $errors) {
            return Redirect::back()->withErrors($errors->get());
        } catch (Exception $e) {
            return Redirect::back()->withErrors(array('exception' => $e->getMessage()));
        }

        switch ($response) {
            case Password::PASSWORD_RESET:
                return Redirect::to('login')->with('message', 'Password reset!');
            case Password::INVALID_PASSWORD:
            case Password::INVALID_TOKEN:
            case Password::INVALID_USER:
            default:
                return Redirect::back()->withErrors(array('reset' => Lang::get($response)));
        }
    }

}
