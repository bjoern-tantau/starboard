<h1>{{{ $game->name }}}</h1>

<h2>{{{ trans('Build the galaxy!') }}}</h2>

<section id="galaxy"></section>

@if($game->activePlayer->id == $player->id)
{{ Form::model($game, array('action' => array('GameController@putPlanet', $game->id), 'method' => 'PUT')) }}
{{ Form::hidden('planet_id', null, array('id' => 'planet_id')) }}
{{ Form::hidden('planet_x_position', null, array('id' => 'planet_x_position')) }}
{{ Form::hidden('planet_y_position', null, array('id' => 'planet_y_position')) }}
{{ Form::submit(trans('Place Planet'), array('id' => 'submit_planet', 'disabled' => 'disabled')) }}
{{ Form::close() }}
@endif

@section('head')
<script type="text/javascript">
    $(document).ready(function() {
        var stage = new Kinetic.Stage({
            container: $('#galaxy')[0],
            width: 800,
            height: 500
        });
        var background = new Kinetic.Layer({id: 'background'});
        stage.add(background);
        background.add(new Kinetic.Rect({
            width: stage.width(),
            height: stage.height(),
            fillRadialGradientStartPoint: {
                x: stage.width() / 2,
                y: stage.height() / 2
            },
            fillRadialGradientEndRadius: stage.width(),
            fillRadialGradientColorStops: [0, '#111', 1, '#000'],
        }));
        for (var i = 0; i < stage.width(); i++) {
            var y_pos = Math.floor(Math.random() * (stage.height()));
            var min_opacity = 0.2;
            var max_opacity_pos = stage.height() * 0.5;
            var max_opacity_height = stage.height() * 2;
            background.add(new Kinetic.Circle({
                x: Math.floor(Math.random() * stage.width()),
                y: y_pos,
                radius: Math.floor(Math.random() * 10) / 8,
                fill: "#eee",
                opacity: 3 * Math.exp(-Math.pow((y_pos - max_opacity_pos), 2) / max_opacity_height) + min_opacity
            }));
        }

        var galaxy = new Kinetic.Layer({id: 'galaxyLayer', draggable: true});
        var galaxyBackground = new Kinetic.Rect({
            width: stage.width(),
            height: stage.height(),
            opacity: 0
        });
        function resizeGalaxyBackground(e) {
            var pos = galaxy.position();
            var scale = galaxy.scale();
            var newOffset = {
                x: pos.x / scale.x,
                y: pos.y / scale.y
            };
            galaxyBackground.offset(newOffset);
            galaxyBackground.width(stage.width() / scale.x);
            galaxyBackground.height(stage.height() / scale.y);
            galaxy.draw();
        }
        galaxy.on('dragend', resizeGalaxyBackground);
        galaxy.add(galaxyBackground);
        stage.add(galaxy);

        $('#galaxy').bind('wheel', function(e) {
            var sensitivity = 16;
            var delta = e.originalEvent.wheelDelta;
            if (delta !== 0) {
                e.preventDefault();
            }
            var oldScale = galaxy.scale().x;
            var newScale = oldScale + delta / (Math.abs(delta * sensitivity));
            galaxy.scale({x: newScale, y: newScale});

            var pointer = stage.getPointerPosition();
            var pos = galaxy.position();
            var newPos = {
                x: (pointer.x * oldScale + pos.x - (pointer.x * newScale)),
                y: (pointer.y * oldScale + pos.y - (pointer.y * newScale))
            };
            galaxy.position(newPos);

            resizeGalaxyBackground();
        });

        var availablePositions = {
            '0': {
                '0': true
            }
        };

        // @if($game->activePlayer->id == $player->id)

        var newPlanets = (<?php echo $player->planets->toJson() ?>);
        var newPlanetsLayer = new Kinetic.Layer({id: 'newPlanets'});
        stage.add(newPlanetsLayer);
        var clones = [];
        $.each(newPlanets, function(index, planet) {
            var planetGroup = new Kinetic.Group({
                x: 120 * index + 60,
                y: stage.height() - 60
            });
            var circle = new Kinetic.Circle({
                radius: 50,
                fill: planet.planet.color
            });
            planetGroup.add(circle);
            var name = new Kinetic.Text({
                text: planet.planet.name,
                fill: 'black',
                fontSize: 18,
                fontVariant: 'small-caps',
                shadowColor: 'white',
                shadowBlur: 10
            });
            name.offsetX(name.width() / 2);
            name.offsetY(40);
            planetGroup.add(name);
            newPlanetsLayer.add(planetGroup);
            planetGroup.on('click', function() {
                $.each(clones, function(index, clone) {
                    clone.remove();
                });
                $('#planet_id').val(null);
                $('#planet_x_position').val(null);
                $('#planet_y_position').val(null);
                $('#submit_planet').attr('disabled', 'disabled');
                $.each(availablePositions, function(x, yPositions) {
                    $.each(yPositions, function(y) {
                        var clone = planetGroup.clone({
                            opacity: 0.5
                        });
                        clone.returnOpacity = clone.getAttr('opacity');
                        clone.xPlanetPosition = x;
                        clone.yPlanetPosition = y;
                        clone.off('click');
                        clone.setAbsolutePosition({x: (stage.width() / 2) + (x * (circle.getAttr('radius') * 2 + 5)), y: (stage.height() / 2) + (y * (circle.getAttr('radius') * 2 + 5))});
                        clone.on('mouseover', function() {
                            this.opacity(1);
                            galaxy.draw();
                        });
                        clone.on('mouseout', function() {
                            this.opacity(clone.returnOpacity);
                            galaxy.draw();
                        });
                        clone.on('click', function() {
                            $.each(clones, function(index, clone) {
                                clone.opacity(0.5);
                                clone.returnOpacity = clone.getAttr('opacity');
                            });
                            this.opacity(1);
                            this.returnOpacity = clone.getAttr('opacity');
                            $('#planet_id').val(planet.id);
                            $('#planet_x_position').val(this.xPlanetPosition);
                            $('#planet_y_position').val(this.yPlanetPosition);
                            $('#submit_planet').removeAttr('disabled');
                            galaxy.draw();
                        });
                        galaxy.add(clone);
                        clones.push(clone);
                    });
                });
                galaxy.draw();
            });
        });
        // @endif

        stage.draw();
    });
</script>
@stop