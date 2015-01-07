<h1>{{{ $game->name }}}</h1>

<h2>{{{ trans('Build the galaxy!') }}}</h2>

<section id="galaxy" style="height: 600px;"></section>

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
            width: $('#galaxy').width(),
            height: $('#galaxy').height()
        });

        var background = new Kinetic.Layer({id: 'background'});
        var backgroundGroup = new Kinetic.Group();
        backgroundGroup.add(new Kinetic.Rect({
            width: stage.width(),
            height: stage.height(),
            fill: 'black'
        }));
        for (var i = 0; i < stage.width(); i++) {
            var y_pos = Math.floor(Math.random() * (stage.height()));
            var min_opacity = 0.2;
            var max_opacity_pos = stage.height() * 0.5;
            var max_opacity_height = stage.height() * 2;
            backgroundGroup.add(new Kinetic.Circle({
                x: Math.floor(Math.random() * stage.width()),
                y: y_pos,
                radius: Math.floor(Math.random() * 10) / 8,
                fill: "#eee",
                opacity: 3 * Math.exp(-Math.pow((y_pos - max_opacity_pos), 2) / max_opacity_height) + min_opacity
            }));
        }
        background.add(backgroundGroup);
        background.add(backgroundGroup.clone({
            x: stage.width() - 1
        }))
        background = background.cache({
            width: stage.width() * 2,
            height: stage.height()
        });
        stage.add(background);
        var animation = new Kinetic.Animation(function(frame) {
            var velocity = 5;
            var dist = velocity * (frame.timeDiff / 1000);
            var newX = background.x() - dist;
            if (newX > (-stage.width())) {
                background.x(newX);
            } else {
                background.x(0);
            }
        }, [background]);
        animation.start();

        var galaxy = new Kinetic.Layer({id: 'galaxyLayer', draggable: true});
        var galaxyDragger = new Kinetic.Rect({
            width: stage.width(),
            height: stage.height(),
            opacity: 0
        });
        function resizeGalaxyDragger(e) {
            var pos = galaxy.position();
            var scale = galaxy.scale();
            var newOffset = {
                x: pos.x / scale.x,
                y: pos.y / scale.y
            };
            galaxyDragger.offset(newOffset);
            galaxyDragger.width(stage.width() / scale.x);
            galaxyDragger.height(stage.height() / scale.y);
            galaxy.draw();
        }
        galaxy.on('dragend', resizeGalaxyDragger);
        galaxy.add(galaxyDragger);
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

            resizeGalaxyDragger();
        });

        function createPlanet(planet, config) {
            var config = config || {};
            var planetGroup = new Kinetic.Group(config);
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
            return planetGroup;
        }

        var availablePositions = {
            '0': {
                '0': true
            }
        };
        var occupiedPositions = {};

        var activePlanets = (<?php echo $game->planets->toJson() ?>);
        $.each(activePlanets, function(index, planet) {
            var planetGroup = createPlanet(planet, {
                id: 'planet' + planet.id,
                x: stage.width() / 2 + planet.x_position * 105,
                y: stage.height() / 2 - planet.y_position * 105
            });
            if (!occupiedPositions[planet.x_position]) {
                occupiedPositions[planet.x_position] = {};
            }
            occupiedPositions[planet.x_position][planet.y_position] = planet;
            var routes = planet.planet.routes;
            var start = 0;
            if (routes == 2) {
                $.each(activePlanets, function(index, activePlanet) { // Hooray for function scopes.
                    if (activePlanet.x_position == planet.x_position) {
                        if (
                                activePlanet.y_position - 1 == planet.y_position ||
                                activePlanet.y_position + 1 == planet.y_position
                                ) {
                            start = 2; // vertically turned planet
                            routes = 4;
                            return false;
                        }
                    }
                });
            } else if (routes == 3) {
                $.each(activePlanets, function(index, activePlanet) { // Hooray for function scopes.
                    if (activePlanet.x_position == planet.x_position) {
                        if (activePlanet.y_position + 1 == planet.y_position) {
                            routes = 2; // Only allow routes to left and right if taken route is down.
                            return false;
                        }
                    }
                });
            }
            for (var i = start; i < routes; i++) {
                switch (i) {
                    case 0: // LEFT
                        var availableX = planet.x_position - 1;
                        var availableY = planet.y_position;
                        break;
                    case 1: // RIGHT
                        var availableX = planet.x_position + 1;
                        var availableY = planet.y_position;
                        break;
                    case 2: // UP
                        var availableX = planet.x_position;
                        var availableY = planet.y_position + 1;
                        break;
                    case 3: // DOWN
                        var availableX = planet.x_position;
                        var availableY = planet.y_position - 1;
                        break;
                    default: // A B A
                        alert('Konami!');
                        break;
                }
                if (!availablePositions[availableX]) {
                    availablePositions[availableX] = {};
                }
                availablePositions[availableX][availableY] = true;
            }
            galaxy.add(planetGroup);
        });
        $.each(occupiedPositions, function(xPosition, yPositions) {
            $.each(yPositions, function(yPosition, planet) {
                if (availablePositions[xPosition]) {
                    delete availablePositions[xPosition][yPosition];
                }
            });
        });
        galaxy.draw();

        // @if($game->activePlayer->id == $player->id)

        var newPlanets = (<?php echo $player->planets->toJson() ?>);
        var newPlanetsLayer = new Kinetic.Layer({id: 'newPlanets'});
        stage.add(newPlanetsLayer);
        var clones = [];
        $.each(newPlanets, function(index, planet) {
            if (planet.x_position === null || planet.y_position === null || planet.x_position === undefined || planet.y_position === undefined) {
                var planetGroup = createPlanet(planet, {
                    x: 120 * index + 60,
                    y: stage.height() - 60
                });

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
                            clone.setAbsolutePosition({x: (stage.width() / 2) + (x * (50 * 2 + 5)), y: (stage.height() / 2) - (y * (50 * 2 + 5))});
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
            }
        });
        // @endif

        stage.draw();
    });
</script>
@stop