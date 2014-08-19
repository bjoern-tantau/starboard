<h1>{{{ trans('Games') }}}</h1>

{{ link_to_action('GameController@getCreate', trans('Create New Game')) }}

<h2>{{{ trans('Games I am playing') }}}</h2>

<table>
    <thead>
        <tr>
            <th>{{{ trans('Name') }}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($gamesPlaying as $game)
        <tr>
            <td>{{ link_to_action('GameController@getShow', $game->name, $game->id) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<h2>{{{ trans('My Games') }}}</h2>

<table>
    <thead>
        <tr>
            <th>{{{ trans('Name') }}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ownGames as $game)
        <tr>
            <td>{{ link_to_action('GameController@getShow', $game->name, $game->id) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<h2>{{{ trans('Games I can join') }}}</h2>

<table>
    <thead>
        <tr>
            <th>{{{ trans('Name') }}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($openGames as $game)
        <tr>
            <td>{{ link_to_action('GameController@getShow', $game->name, $game->id) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>