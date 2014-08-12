<h1>{{{ $user->name }}}</h1>
@if($canEdit)
    {{ link_to_route('user.edit', trans('Edit User'), $user->id) }}
    {{ Form::model($user, array('route' => array('user.destroy', $user->id), 'method' => 'DELETE')) }}
        {{ Form::submit('Delete User') }}
    {{ Form::close() }}
@endif
<table class="user">
    <tbody>
        <tr>
            <th scope="row">{{{ trans('Username') }}}</th>
            <td>{{{ $user->name }}}</td>
        </tr>
        <tr>
            <th scope="row">{{{ trans('E-Mail') }}}</th>
            <td>{{{ $user->email }}}</td>
        </tr>
    </tbody>
</table>