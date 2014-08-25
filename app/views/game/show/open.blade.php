<h1>{{{ $game->name }}}</h1>

<ol class="players">
    @foreach($game->players as $player)
    <li class="player" id="player-{{ $player->id }}" data-faction="{{{ $player->faction_type }}}" style="color:{{{ $player->faction->color }}}">
        <span class="user-name">{{{ $player->user->name }}}</span>
        @if($player->user_id == Auth::id())
        {{ Form::model($player, array('route' => array('player.update', $player->id), 'method' => 'PUT')) }}
        <select class="faction_type" name="faction_type" style="color:{{{ $player->faction->color }}}">
            @foreach ($player->factions as $type => $faction)
            <option value="{{{ $type }}}" style="color:{{{ $faction->color }}};" data-color="{{{ $faction->color }}}" {{ $player->factionType == $type ? 'selected="selected"' : '' }}>{{{ $faction->name }}}</option>
            @endforeach
        </select>
        {{ Form::close() }}
        @else
        <span class="faction-name">{{{ $player->faction->name }}}</span>
        @endif
    </li>
    @endforeach
</ol>

@if($game->owner_id == Auth::id())
{{ Form::model($game, array('action' => array('GameController@putUpdate', $game->id), 'method' => 'PUT')) }}
{{ Form::hidden('state', Game::STATE_SETUP_GALAXY) }}
{{ Form::submit(trans('Setup Galaxy'), array('id' => 'submit_setup')) }}
{{ Form::close() }}
@endif

@section('head')
<script type="text/javascript">
    $(document).ready(function() {
        function updateSubmitSetup() {
            if ($('#submit_setup')) {
                var selectedFactions = [];
                $('#submit_setup').removeAttr('disabled');
                $('li.player').each(function() {
                    var faction = $(this).data('faction');
                    if (selectedFactions[faction]) {
                        $('#submit_setup').attr('disabled', 'disabled');
                    }
                    selectedFactions[faction] = true;
                });
            }
        }
        ;
        updateSubmitSetup();
        function updatePlayer(data) {
            if (data.objects && data.objects.player) {
                var player = data.objects.player;
                var li = $('#player-' + player.id);
                if (li) {
                    li.data('faction', player.faction_type);
                    li.css('color', player.faction.color);
                    li.find('.faction-name').text(player.faction.name);
                }
            }
            updateSubmitSetup();
        }
        window.conn = new ab.Session(
                'ws://<?php echo parse_url(url(), PHP_URL_HOST) ?>:<?php echo Config::get('latchet::socketPort', '1111') ?>',
                function() { // Once the connection has been established
                    window.conn.subscribe('game/{{ $game->id }}', function(topic, data) {
                        updatePlayer(data);
                    });
                },
                function() {
                    // When the connection is closed
                    console.log('WebSocket connection closed');
                },
                {
                    // Additional parameters, we're ignoring the WAMP sub-protocol for older browsers
                    'skipSubprotocolCheck': true
                }
        );
        $('.faction_type').change(function(e) {
            var color = $(this).find(':selected').data('color');
            var li = $(this).parents('li');
            $(this).css('color', color);
            li.css('color', color).data('faction', $(this).val());
            var form = $(this).parents('form');
            var xhr = $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(data) {
                    updatePlayer(data);
                }
            });
            updateSubmitSetup();
        });
    });
</script>
@stop