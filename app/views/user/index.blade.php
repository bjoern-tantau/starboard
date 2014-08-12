<h1>Users</h1>
<table class="user">
    <thead>
        <tr>
            <th scope="col">{{{ trans('Username') }}}</th>
            <th scope="col">{{{ trans('Actions') }}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>{{ link_to_route('user.show', $user->name, $user->id) }}</td>
            <td>
                {{ link_to_route('user.edit', trans('Edit'), $user->id) }}
                {{ Form::model($user, array('route' => array('user.destroy', $user->id), 'method' => 'DELETE')) }}
                    {{ Form::submit('Delete User') }}
                {{ Form::close() }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>