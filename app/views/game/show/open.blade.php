<h1>{{{ $game->name }}}</h1>

<ol class="players">
    @foreach($game->players as $player)
    <li class="player" id="player-{{ $player->id }}" style="color:{{{ $player->faction->color }}}">
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

@section('head')
<script type="text/javascript">
    $(document).ready(function() {
        function updatePlayer(data) {
            if (data.objects && data.objects.player) {
                var player = data.objects.player;
                var li = $('#player-' + player.id);
                if (li) {
                    li.css('color', player.faction.color);
                    li.find('.faction-name').text(player.faction.name);
                }
            }
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
            $(this).css('color', color).parents('li').css('color', color);
            var form = $(this).parents('form');
            var xhr = $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(data) {
                }
            });
        });
    });
</script>
@stop