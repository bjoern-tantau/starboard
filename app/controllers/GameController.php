<?php

class GameController extends BaseController
{

    /**
     * Instantiate a new GameController instance.
     */
    public function __construct()
    {
        $this->beforeFilter('auth');
        $this->beforeFilter('csrf', array('on' => 'post'));
    }

    /**
     * Show list of available games.
     *
     * @return Response
     */
    public function getIndex()
    {
        $openGames = Game::open()->get();
        $ownGames = Auth::user()->ownGames;
        $gamesPlaying = Auth::user()->games;
        $this->layout->content = View::make('game.index', array(
                'openGames'    => $openGames,
                'ownGames'     => $ownGames,
                'gamesPlaying' => $gamesPlaying,
        ));
        return;
    }

    /**
     * Create a new game.
     *
     * @param integer $id Game ID.
     * @return Response
     */
    public function getCreate($id = null)
    {
        if (!is_null($id)) {
            $game = Game::find($id);
        } else {
            $game = new Game(array('owner' => Auth::user()));
        }
        if (!$game) {
            return App::abort(404);
        }
        $this->layout->content = View::make('game.create', array(
                'game' => $game,
        ));
        return;
    }

    /**
     * Store newly created game.
     *
     * @return Response
     */
    public function postStore()
    {
        $game = new Game(Input::all());
        $game->owner = Auth::user();
        $game->state = Game::STATE_OPEN;
        if ($game->save()) {
            return Redirect::action('GameController@getShow', $game->id)->with('message', 'Game started.');
        } else {
            return Redirect::back()->withErrors($game->errors());
        }
    }

    /**
     * Show a game.
     *
     * @return Response
     */
    public function getShow($id = null)
    {
        $game = Game::find($id);
        if (!$game) {
            return App::abort(404);
        }
        switch ($game->state) {
            case Game::STATE_SETUP:
                return Redirect::action('GameController@getCreate', $game->id);
            case Game::STATE_OPEN:
                $player = Player::firstOrNew(array('game_id' => $game->id, 'user_id' => Auth::user()->id,));
                if (!$player->id) {
                    $player->factionType = $game->nextFactionType;
                    if (count($game->players) >= $game->maxPlayers) {
                        return Redirect::action('GameController@getIndex')->withErrors(array('game' => array("The game " . $game->name . " is full.")));
                    }
                    $player->save();
                    $game->players->add($player);
                }
                $this->layout->content = View::make('game.show.open', array(
                        'game'   => $game,
                        'player' => $player,
                ));
                break;
            case Game::STATE_SETUP_GALAXY:
            case Game::STATE_SETUP_GALAXY_REVERSE:
                $player = Player::firstOrNew(array('game_id' => $game->id, 'user_id' => Auth::user()->id,));
                foreach ($player->planets as $planet) {
                    $planet->planet = $planet->planet; // Add XML
                }
                $this->layout->content = View::make('game.show.galaxy', array(
                        'game'   => $game,
                        'player' => $player,
                ));
                break;
            default:
                $this->layout->content = View::make('game.show.index', array(
                        'game' => $game,
                ));
                break;
        }
        return;
    }

    /**
     * Update game data.
     *
     * @return Response
     */
    public function putUpdate($id = null)
    {
        $game = Game::find($id);
        if (!$game) {
            return App::abort(404);
        }
        switch (Input::get('state')) {
            case Game::STATE_SETUP:
                return Redirect::action('GameController@getCreate', $game->id);
            case Game::STATE_OPEN:
                break;
            case Game::STATE_SETUP_GALAXY:
                if (count($game->players) < 2) {
                    return Redirect::action('GameController@getShow', $game->id)->withErrors(array('players' => array("You need at least 2 players in the game.")));
                }
                if (count($game->players) > $game->maxPlayers) {
                    return Redirect::action('GameController@getShow', $game->id)->withErrors(array('players' => array("There can't be more than " . $game->maxPlayers . " players in the game.")));
                }
                $usedFactions = array();
                foreach ($game->players as $player) {
                    if (isset($usedFactions[$player->factionType])) {
                        return Redirect::action('GameController@getShow', $game->id)->withErrors(array('players' => array('No two players may have the same faction.')));
                    }
                    $usedFactions[$player->factionType] = true;
                }
                $planetTypes = Planet::getRandomPlanetTypes($game);
                $planetsPerPlayer = Config::get('game.planets_per_player');
                $i = 0;
                foreach ($game->players as $player) {
                    $types = array_slice($planetTypes, $i, $planetsPerPlayer);
                    foreach ($types as $type) {
                        Planet::create(array(
                            'player'      => $player,
                            'planet_type' => $type,
                        ));
                    }
                    $i = $i + $planetsPerPlayer;
                }
                $game->activePlayer = $game->nextPlayer;
                $game->state = Game::STATE_SETUP_GALAXY;
                $game->save();
                break;
            default:
                break;
        }
        return Redirect::action('GameController@getShow', $game->id);
    }

    /**
     * Update planet data.
     *
     * @return Response
     */
    public function putPlanet($gameId)
    {
        $game = Game::find($gameId);
        if (!$game) {
            return App::abort(404);
        }

        if ($game->activePlayer->user_id != Auth::id()) {
            return Redirect::action('GameController@getShow', $game->id)->withErrors(array('player' => array('Only the active player may place a planet.')));
        }

        $planetId = Input::get('planet_id');
        $planet = Planet::find($planetId);
        if (!$planet) {
            return Redirect::action('GameController@getShow', $game->id)->withErrors(array('planet' => array('The specified planet does not exist.')));
        } elseif ($game->activePlayer->id != $planet->player_id) {
            return Redirect::action('GameController@getShow', $game->id)->withErrors(array('planet' => array('The specified planet does not belong to the current player.')));
        } elseif (!is_null($planet->xPosition) && !is_null($planet->yPosition)) {
            return Redirect::action('GameController@getShow', $game->id)->withErrors(array('planet' => array('The specified planet has already been placed.')));
        }

        $xPosition = Input::get('planet_x_position');
        $yPosition = Input::get('planet_y_position');
        if (!is_numeric($xPosition) || !is_numeric($yPosition)) {
            return Redirect::action('GameController@getShow', $game->id)->withErrors(array('planet' => array('Invalid coordinates provided.')));
        }

        $neighbors = array();

        foreach ($game->planets as $activePlanet) {
            if ($activePlanet->xPosition == $xPosition && $activePlanet->yPosition == $yPosition) {
                return Redirect::action('GameController@getShow', $game->id)->withErrors(array('planet' => array('Invalid coordinates provided.')));
            }
            for ($i = 0; $i < $activePlanet->planet->routes; $i++) {
                switch ($i) {
                    case 0: // LEFT
                        $activeX = $activePlanet->xPosition - 1;
                        $activeY = $activePlanet->yPosition;
                        break;
                    case 1: // RIGHT
                        $activeX = $activePlanet->xPosition + 1;
                        $activeY = $activePlanet->yPosition;
                        break;
                    case 2: // UP
                        $activeX = $activePlanet->xPosition;
                        $activeY = $activePlanet->yPosition + 1;
                        break;
                    case 3: // DOWN
                        $activeX = $activePlanet->xPosition;
                        $activeY = $activePlanet->yPosition - 1;
                        break;
                }
                if ($activeX == $xPosition && $activeY == $yPosition) {
                    $neighbors[] = $activePlanet;
                }
            }
        }

        if (((empty($neighbors) || count($game->planets) == 0) && ($xPosition != 0 || $yPosition != 0))) {
            return Redirect::action('GameController@getShow', $game->id)->withErrors(array('planet' => array('Invalid coordinates provided.')));
        }

        foreach ($neighbors as $neighbor) {
            NavigationRoute::create(array('planet1' => $neighbor, 'planet2' => $planet));
        }
        $planet->xPosition = $xPosition;
        $planet->yPosition = $yPosition;
        $planet->save();

        $game->activePlayer = $game->nextPlayer;
        if ($game->activePlayer->id == $game->players->first()->id) {
            $game->state = Game::STATE_SETUP_GALAXY_REVERSE;
        } elseif ($game->activePlayer->id == $game->players->last()->id) {
            $game->state = Game::STATE_SETUP_GALAXY;
        }
        $game->save();

        return Redirect::action('GameController@getShow', $game->id);
    }

}
