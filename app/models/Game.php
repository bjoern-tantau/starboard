<?php

/**
 * @property string $name Name
 * @property string $type Type of Game (used to load configuration XML)
 * @property integer $state State of the Game
 * @property integer $maxPlayers Maximum number of Players.
 * @property User $owner Creator of the Game.
 * @property SimpleXMLElement $config Configuration XML
 */
class Game extends GameBase
{

    /**
     *  Attributes available for mass-filling.
     *
     * @var array
     */
    protected $fillable = array(
        'name',
        'type',
        'state',
        'max_players',
    );

    /**
     * Default Attributes.
     *
     * @var array
     */
    protected $attributes = array(
        'state' => 0,
    );

    /**
     * Rules for validation.
     *
     * @var array
     */
    public static $rules = array(
        'owner_id' => 'required',
        'type' => 'type_exists',
    );

    /**
     * Create a new Game model instance.
     *
     * @param array $attributes
     * @return Game
     */
    public function __construct(array $attributes = array())
    {
        $this->type = Config::get('game.default_type');
        $this->name = $this->config->name;
        $this->maxPlayers = $this->config->characters->children()->count();
        parent::__construct($attributes);
        return $this;
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        Validator::extend('type_exists', 'Game@validateTypeExists', 'The specified type is not available.');
    }

    /**
     * Associate owner with user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('User', 'owner_id');
    }

    /**
     * Set owner_id from given user.
     *
     * @param User $user
     * @return void
     */
    public function setOwnerAttribute(User $user)
    {
        $this->owner_id = $user->id;
    }

    /**
     * Get config XML
     *
     * @return SimpleXMLElement
     */
    public function getConfigAttribute()
    {
        return self::getXml($this->type)->{$this->type};
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
        $xml = self::getXml($value);
        return $xml->{$value}->count() == 1;
    }
}
