<?php

/**
 * @property Planet $planet1 Planet linking to this route.
 * @property Planet $planet2 Other planet linking to this route.
 * @property Planet $planets All planets associated with this route.
 */
class NavigationRoute extends GameBase
{

    /**
     *  Attributes available for mass-filling.
     *
     * @var array
     */
    protected $fillable = array(
        'planet1',
        'planet1_id',
        'planet2',
        'planet2_id',
    );

    /**
     * Rules for validation.
     *
     * @var array
     */
    public static $rules = array(
        'planet1_id' => 'required',
        'planet2_id' => 'different:planet1_id',
    );

    /**
     * Associate route with planet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function planet1()
    {
        return $this->belongsTo('Planet');
    }

    /**
     * Associate route with planet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function planet2()
    {
        return $this->belongsTo('Planet');
    }

    /**
     * Set planet_id from given planet.
     *
     * @param Planet $planet
     * @return void
     */
    public function setPlanet1Attribute(Planet $planet)
    {
        $this->planet1_id = $planet->id;
    }

    /**
     * Set planet_id from given planet.
     *
     * @param Planet $planet
     * @return void
     */
    public function setPlanet2Attribute(Planet $planet)
    {
        $this->planet2_id = $planet->id;
    }

    /**
     * Get planets associations.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPlanetsAttribute()
    {
        $ids = array();
        if ($this->planet1_id) {
            $ids[] = $this->planet1_id;
        }
        if ($this->planet2_id) {
            $ids[] = $this->planet2_id;
        }
        return Planet::whereIn('id', $ids)->get();
    }

}
