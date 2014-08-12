<h1>{{{ $user->id ? $user->name : trans('Create User') }}}</h1>
@if($user->id)
    {{ link_to_route('user.show', trans('Show User'), $user->id) }}
    {{ Form::model($user, array('route' => array('user.destroy', $user->id), 'method' => 'DELETE')) }}
        {{ Form::submit('Delete User') }}
    {{ Form::close() }}
@endif
@if(isset($action))
    {{ Form::model($user, array('action' => $action)) }}
@elseif($user->id)
    {{ Form::model($user, array('route' => array('user.update', $user->id), 'method' => 'PUT')) }}
@else
    {{ Form::model($user, array('route' => array('user.store'), 'method' => 'POST')) }}
@endif
<fieldset>
    <legend>{{{ $user->id ? trans('Edit User') : trans('Create User') }}}</legend>
    <ol class="login">
        <li class="required{{ $errors->has('name') ? ' error' : '' }}">
            {{ Form::label('name', trans('Username')) }}
            {{ Form::text('name') }}
            @errors('name')
        </li>
        <li class="required{{ $errors->has('email') ? ' error' : '' }}">
            {{ Form::label('email', trans('E-Mail')) }}
            {{ Form::email('email') }}
            @errors('email')
        </li>
        <li class="required{{ $errors->has('password') ? ' error' : '' }}">
            {{ Form::label('password', trans('Password')) }}
            {{ Form::password('password') }}
            @errors('password')
        </li>
        <li class="required{{ $errors->has('password_confirmation') ? ' error' : '' }}">
            {{ Form::label('password_confirmation', trans('Password Confirmation')) }}
            {{ Form::password('password_confirmation') }}
            @errors('password_confirmation')
        </li>
    </ol>
    {{ Form::submit($user->id ? trans('Save User') : trans('Create User')) }}
</fieldset>
{{ Form::close() }}