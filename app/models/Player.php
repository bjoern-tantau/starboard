<?php

/**
 * @property integer $user_id ID of associated User.
 * @property User $user User
 * @property integer $game_id ID of associated Game.
 * @property Game $game Game
 * @property string $characterType Character Type from XML.
 * @property boolean $active Is Player still alive?
 * @property SimpleXMLElement $character Character XML data.
 */
class Player extends GameBase
{

    /**
     *  Attributes available for mass-filling.
     *
     * @var array
     */
    protected $fillable = array(
        'user',
        'game',
        'character_type',
    );

    /**
     * Default Attributes.
     *
     * @var array
     */
    protected $attributes = array(
        'character_type' => 'jack',
        'active'         => true,
    );

    /**
     * Rules for validation.
     *
     * @var array
     */
    public static $rules = array(
        'user_id'        => 'required',
        'game_id'        => 'required',
        'character_type' => 'character_type_exists',
    );

    public function validate(array $rules = array(), array $customMessages = array())
    {
        $player = $this;
        Validator::extend('character_type_exists', function ($attribute, $value, $parameters) use($player) {
            return $player->validateTypeExists($attribute, $value);
        }
            , 'The specified character type is not available.');
        return parent::validate($rules, $customMessages);
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
    }

    /**
     * Associate player with user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    /**
     * Set user_id from given user.
     *
     * @param User $user
     * @return void
     */
    public function setUserAttribute(User $user)
    {
        $this->user_id = $user->id;
    }

    /**
     * Associate player with game.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function game()
    {
        return $this->belongsTo('Game');
    }

    /**
     * Set game_id from given Game.
     *
     * @param Game $game
     * @return void
     */
    public function setGameAttribute(Game $game)
    {
        $this->game_id = $game->id;
    }

    /**
     * Get character attribute.
     *
     * @return SimpleXMLElement
     */
    public function getCharacterAttribute()
    {
        if ($game = $this->game) {
            $xml = self::getXml($game->type);
            if ($xml && $xml->{$game->type} && $xml->{$game->type}->characters) {
                return $xml->{$game->type}->characters->{$this->characterType};
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
        if ($character = $this->character) {
            return $character->count() == 1;
        }
        return false;
    }

}
