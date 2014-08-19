<?php

/**
 * @property string $name Name
 * @property string $type Type of Game (used to load configuration XML)
 * @property integer $state State of the Game
 * @property integer $maxPlayers Maximum number of Players.
 * @property integer $defaultMaxPlayers Default value of $maxPlayers.
 * @property User $owner Creator of the Game.
 * @property SimpleXMLElement $config Configuration XML
 * @property array $availableTypes Available Game Types.
 * @property string $nextFactionType Next available unused faction type.
 */
class Game extends GameBase
{

    const STATE_SETUP = 0;
    const STATE_OPEN = 1;

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
        'owner',
        'owner_id',
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
        'type'     => 'type_exists',
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
        $this->maxPlayers = $this->defaultMaxPlayers;
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
     * Associate players with game.
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function players()
    {
        return $this->hasMany('Player');
    }

    /**
     * Associate users through players with game.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function users()
    {
        return $this->belongsToMany('User', 'players');
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
     * Get default number of $maxPlayers.
     *
     * @return integer
     */
    public function getDefaultMaxPlayersAttribute()
    {
        return $this->config->factions->children()->count();
    }

    /**
     * Get game types available.
     *
     * @return array
     */
    public function getAvailableTypesAttribute()
    {
        $types = array();
        $dir = app_path(Config::get('game.config_dir'));
        $dirs = File::directories($dir);
        foreach ($dirs as $directory) {
            $type = basename($directory);
            if ($xml = self::getXml($type)->{$type}) {
                $types[$type] = $xml->name;
            }
        }
        return $types;
    }

    /**
     * Get next unused player faction type.
     *
     * @return string
     */
    public function getNextFactionTypeAttribute()
    {
        $factions = array();
        foreach ($this->config->factions->children() as $type => $faction) {
            $factions[$type] = $faction;
        }

        reset($factions);
        $firstType = key($factions);

        foreach ($this->players as $player) {
            unset($factions[$player->factionType]);
        }
        if (!empty($factions)) {
            return key($factions);
        }
        return $firstType;
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

    /**
     * Return all games in a certain state.
     *
     * @param LaravelBook\Ardent\Builder $builder
     * @param int|array $state
     * @returns LaravelBook\Ardent\Builder
     */
    public function scopeState($builder, $state)
    {
        if (!is_array($state)) {
            $state = array($state);
        }
        return $builder->whereIn('state', $state);
    }

    /**
     * Return all games in a open state.
     *
     * @param LaravelBook\Ardent\Builder $builder
     * @param int|array $state
     * @returns LaravelBook\Ardent\Builder
     */
    public function scopeOpen($builder)
    {
        return $builder->where('state', self::STATE_OPEN);
    }

}
