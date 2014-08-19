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
            case GAME::STATE_SETUP:
                return Redirect::action('GameController@getCreate', $game->id);
            case GAME::STATE_OPEN:
                $this->layout->content = View::make('game.show.open', array(
                        'game' => $game,
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

}
