<h1>{{{ $game->name }}}</h1>

<ol class="players">
    @foreach($game->players as $player)
    <li class="player" style="color:{{{ $player->faction->color }}}">
        {{{ $player->user->name }}}
        @if($player->user_id == Auth::id())
        <select id="faction_type" name="faction_type" style="color:{{{ $player->faction->color }}}">
            @foreach ($player->factions as $type => $faction)
            <option value="{{{ $type }}}" style="color:{{{ $faction->color }}}" {{ $player->factionType == $type ? 'selected="selected"' : '' }}>{{{ $faction->name }}}</option>
            @endforeach
        </select>
        @else
        {{{ $player->faction->name }}}
        @endif
    </li>
    @endforeach
</ol>

@section('head')
<script type="text/javascript">
    $(document).ready(function() {
        $('#faction_type').change(function(e) {
            var color = $(this).find(':selected').css('color');
            $(this).css('color', color).parent('li').css('color', color);
        });
    });
</script>
@stop