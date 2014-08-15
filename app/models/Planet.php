<?php

/**
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
     * Set playder_id from given player.
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

}
