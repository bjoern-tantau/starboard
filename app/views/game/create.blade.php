<h1>{{{ trans('Create Game') }}}</h1>

{{ Form::model($game, array('action' => array('GameController@postStore'), 'method' => 'POST')) }}
<fieldset>
    <legend>{{{ trans('Create Game') }}}</legend>
    <ol class="login">
        <li class="required{{ $errors->has('name') ? ' error' : '' }}">
            {{ Form::label('name', trans('Name')) }}
            {{ Form::text('name') }}
            @errors('name')
        </li>
        <li class="required{{ $errors->has('type') ? ' error' : '' }}">
            {{ Form::label('type', trans('Type')) }}
            {{ Form::select('type', $game->availableTypes) }}
            @errors('type')
        </li>
        <li class="required{{ $errors->has('max_players') ? ' error' : '' }}">
            {{ Form::label('max_players', trans('Maximum Players')) }}
            {{ Form::input('number', 'max_players', null, array('min' => 2, 'max' => (string) $game->defaultMaxPlayers)) }}
            @errors('max_players')
        </li>
    </ol>
    {{ Form::submit(trans('Start Game')) }}
</fieldset>
{{ Form::close() }}