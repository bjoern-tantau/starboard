<?php

/**
 * @property integer $user_id ID of associated User.
 * @property User $user User
 * @property integer $game_id ID of associated Game.
 * @property Game $game Game
 * @property string $factionType Faction Type from XML.
 * @property boolean $active Is Player still alive?
 * @property SimpleXMLElement $faction Faction XML data.
 * @property SimpleXMLElement $factions Faction XML data of all factions.
 * @property array $availableFactionTypes
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
        'user_id',
        'game',
        'game_id',
        'faction_type',
    );

    /**
     * Default Attributes.
     *
     * @var array
     */
    protected $attributes = array(
        'faction_type' => 'jack',
        'active'       => true,
    );
    public static $relationsData = array(
        'user' => array(self::BELONGS_TO, 'User'),
        'game' => array(self::BELONGS_TO, 'Game'),
    );

    /**
     * Rules for validation.
     *
     * @var array
     */
    public static $rules = array(
        'user_id'      => 'required',
        'game_id'      => 'required',
        'faction_type' => 'faction_type_exists',
    );

    public function validate(array $rules = array(), array $customMessages = array())
    {
        $player = $this;
        Validator::extend('faction_type_exists', function ($attribute, $value, $parameters) use($player) {
            return $player->validateTypeExists($attribute, $value);
        }
            , 'The specified faction type is not available.');
        return parent::validate($rules, $customMessages);
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
     * Get faction attribute.
     *
     * @return SimpleXMLElement
     */
    public function getFactionAttribute()
    {
        if ($factions = $this->factions) {
            return $factions->{$this->factionType};
        }
        return false;
    }

    /**
     * Get factions attribute.
     *
     * @return SimpleXMLElement
     */
    public function getFactionsAttribute()
    {
        if ($game = $this->game) {
            $xml = self::getXml($game->type);
            if ($xml && $xml->{$game->type} && $xml->{$game->type}->factions) {
                return $xml->{$game->type}->factions->children();
            }
        }
        return false;
    }

    /**
     * Get faction types available.
     *
     * @return array
     */
    public function getAvailableFactionTypesAttribute()
    {
        $types = array();

        if ($factions = $this->factions) {
            foreach ($factions as $factionType => $faction) {
                $types[$factionType] = $faction->name;
            }
        }
        return $types;
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
        if ($faction = $this->faction) {
            return $faction->count() == 1;
        }
        return false;
    }

}
