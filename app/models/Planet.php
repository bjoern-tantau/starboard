<?php

/**
 * @property Player $player Player who got the planet at the start of the game.
 * @property string $planetType Planet Type in XML.
 * @property integer $xPosition X Position on Galaxy Grid.
 * @property integer $yPosition Y Position on Galaxy Grid.
 * @property SimpleXMLElement $planet XML Description of Planet.
 * @property NavigationRoute $routes Collection of Navigation Routes associated with this planet.
 * @property Planet $adjacentPlanets Collection of neighbouring planets.
 */
class Planet extends GameBase
{

    /**
     *  Attributes available for mass-filling.
     *
     * @var array
     */
    protected $fillable = array(
        'player',
        'planet_type',
        'x_position',
        'y_position',
    );

    /**
     * Default Attributes.
     *
     * @var array
     */
    protected $attributes = array(
        'planet_type' => 'sol',
    );

    /**
     * Rules for validation.
     *
     * @var array
     */
    public static $rules = array(
        'player_id'   => 'required',
        'planet_type' => 'planet_type_exists',
    );

    /**
     * Associate planet with player.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function player()
    {
        return $this->belongsTo('Player');
    }

    /**
     * Set player_id from given player.
     *
     * @param Player $player
     * @return void
     */
    public function setPlayerAttribute(Player $player)
    {
        $this->player_id = $player->id;
    }

    public function validate(array $rules = array(), array $customMessages = array())
    {
        $player = $this;
        Validator::extend('planet_type_exists', function ($attribute, $value, $parameters) use($player) {
            return $player->validateTypeExists($attribute, $value);
        }
            , 'The specified planet type is not available.');
        return parent::validate($rules, $customMessages);
    }

    /**
     * Get planet attribute.
     *
     * @return SimpleXMLElement
     */
    public function getPlanetAttribute()
    {
        if ($player = $this->player) {
            if ($game = $player->game) {
                $xml = self::getXml($game->type);
                if ($xml && $xml->{$game->type} && $xml->{$game->type}->planets) {
                    return $xml->{$game->type}->planets->{$this->planetType};
                }
            }
        }
        return false;
    }

    /**
     * Validate type existence.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    public function validateTypeExists($attribute, $value)
    {
        if ($planet = $this->planet) {
            return $planet->count() == 1;
        }
        return false;
    }

    /**
     * Get routes associated with this planet.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoutesAttribute()
    {
        return NavigationRoute::where('planet1_id', '=', $this->id)
                ->orWhere('planet1_id', '=', $this->id)
                ->get()
        ;
    }

    /**
     * Get routes associated with this planet.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAdjacentPlanetsAttribute()
    {
        return Planet::leftJoin('navigation_routes AS route1', 'planets.id', '=', 'route1.planet1_id')
                ->leftJoin('navigation_routes AS route2', 'planets.id', '=', 'route2.planet2_id')
                ->where('route1.planet2_id', '=', $this->id)
                ->orWhere('route2.planet1_id', '=', $this->id)
                ->get()
        ;
    }

    /**
     * Is the given planet adjacent to the self?
     *
     * @return boolean
     */
    public function isAdjacent(Planet $planet)
    {
        if (!$this->id || !$planet->id) {
            return false;
        }
        $id1 = $this->id;
        $id2 = $planet->id;
        $query = DB::table('navigation_routes')
            ->where(function(Illuminate\Database\Query\Builder $query) use($id1, $id2) {
                $query->where('planet1_id', $id1)
                ->where('planet2_id', $id2);
            })
            ->orWhere(function($query) use($id1, $id2) {
            $query->where('planet2_id', $id1)
            ->where('planet1_id', $id2);
        })
        ;
        $result = $query->get(array('id'));
        return (bool) $result;
    }

}
