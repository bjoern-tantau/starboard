{{ Form::open(array('action' => 'AuthController@postReset')) }}
<fieldset class="reset">
    <input type="hidden" name="token" value="{{ $token }}">
    <legend>{{{ trans('Reset Password') }}}</legend>
    <ol class="login">
        <li>
            {{ Form::label('email', trans('E-Mail')) }}
            {{ Form::email('email') }}
        </li>
        <li>
            {{ Form::label('password', trans('Password')) }}
            {{ Form::password('password') }}
        </li>
        <li>
            {{ Form::label('password', trans('Password Confirmation')) }}
            {{ Form::password('password_confirmation') }}
        </li>
    </ol>
    {{ Form::submit(trans('Reset Password')) }}
</fieldset>
{{ Form::close() }}