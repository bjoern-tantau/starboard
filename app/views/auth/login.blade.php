{{ Form::open(array('action' => 'AuthController@postLogin')) }}
<fieldset class="login">
    <legend>{{{ trans('Login') }}}</legend>
    <ol class="login">
        <li>
            {{ Form::label('email', trans('E-Mail')) }}
            {{ Form::email('email') }}
        </li>
        <li>
            {{ Form::label('password', trans('Password')) }}
            {{ Form::password('password') }}
        </li>
    </ol>
    <p class="remind">{{ link_to_action('AuthController@getRemind', trans('I forgot my password.')) }}</p>
    {{ Form::submit(trans('Login')) }}
</fieldset>
{{ Form::close() }}