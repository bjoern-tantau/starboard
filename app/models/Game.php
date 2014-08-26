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
 * @property Player $activePlayer Player that is allowed to do a move.
 */
class Game extends GameBase
{

    const STATE_SETUP = 0;
    const STATE_OPEN = 10;
    const STATE_SETUP_GALAXY = 20;
    const STATE_SETUP_GALAXY_REVERSE = 21; // When the first batch of players has placed their planets they will be traversed in reverse order.

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
     * Associate active player with game.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activePlayer()
    {
        return $this->belongsTo('Player', 'active_player_id');
    }

    /**
     * Set active_player_id from given player.
     *
     * @param Player $player
     * @return void
     */
    public function setActivePlayerAttribute(Player $player = null)
    {
        if (is_null($player)) {
            $this->active_player_id = null;
        } else {
            $this->active_player_id = $player->id;
        }
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
     * Get the next player to be active.
     *
     * @return Player
     */
    public function getNextPlayerAttribute()
    {
        $players = $this->players;
        if (is_null($this->activePlayer)) {
            return $players->first();
        }

        if ($this->state == self::STATE_SETUP_GALAXY) {
            if ($this->activePlayerId == $players->last()->id) {
                $players = $players->reverse();
                $this->state = self::STATE_SETUP_GALAXY_REVERSE;
                $this->save();
            }
        } elseif ($this->state == self::STATE_SETUP_GALAXY_REVERSE) {
            if ($this->activePlayerId == $players->first()->id) {
                $this->state = self::STATE_SETUP_GALAXY;
                $this->save();
            } else {
                $players = $players->reverse();
            }
        }

        if ($this->activePlayerId == $players->first()->id) {
            return $players->get(1);
        }
        if ($this->activePlayerId == $players->last()->id) {
            return $players->first();
        }
        $previousPlayer = null;
        foreach ($players as $player) {
            if (!is_null($previousPlayer) && $this->activePlayerId == $previousPlayer->id) {
                return $player;
            }
            $previousPlayer = $player;
        }

        return null;
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
