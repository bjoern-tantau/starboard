<?php

class PlayerController extends \BaseController
{

    /**
     * Instantiate a new UserController instance.
     */
    public function __construct()
    {
        $this->beforeFilter('auth');
        $this->beforeFilter('csrf', array('on' => 'post'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $player = Player::find($id);
        /* @var $player Player */
        if (!$player) {
            return App::abort(404);
        }
        $authUser = Auth::user();
        $canEdit = ($authUser->id == $player->user_id || $authUser->id == $player->game->owner_id);
        $player->fill(Input::only(array(
                'faction_type',
        )));
        if ($canEdit) {
            if ($player->save()) {
                $player->faction = $player->faction; // initialize faction so that it is passed back.
                $message = array(
                    'type'     => 'success',
                    'messages' => array(
                        'Player saved.'
                    ),
                    'objects'  => array(
                        'player' => $player,
                    ),
                );
                Latchet::publish('game/' . $player->game->id, $message);
                if (Request::ajax()) {
                    return Response::json($message);
                }
                return Redirect::action('GameController@getShow', $player->game->id)->with('message', 'Player saved.');
            } else {
                if (Request::ajax()) {
                    return Response::json(array(
                            'type'     => 'error',
                            'messages' => $player->errors(),
                            'objects'  => array(
                                'player' => Player::find($id),
                            ),
                    ));
                }
                return Redirect::action('GameController@getShow', $player->game->id)->withErrors($player->errors());
            }
        } else {
            return Redirect::guest('login');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $player = Player::find($id);
        if (!$player) {
            return App::abort(404);
        }
        $authUser = Auth::user();
        $canEdit = ($authUser->id == $player->user_id || $authUser->id == $player->game->owner_id);

        if ($canEdit) {
            $player->delete();
            if (Request::ajax()) {
                return Response::json(array(
                        'type'     => 'success',
                        'messages' => array(
                            'Deleted player successfully.'
                        ),
                ));
            }
            return Redirect::back()->with('message', 'Deleted player successfully.');
        } else {
            return Redirect::guest('login');
        }
    }

}
