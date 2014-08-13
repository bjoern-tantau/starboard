<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Base implements UserInterface, RemindableInterface
{

    use UserTrait,
        RemindableTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('password', 'remember_token');

    /**
     *  Attributes available for mass-filling.
     *
     * @var array
     */
    protected $fillable = array('email', 'name', 'password', 'password_confirmation');

    /**
     * Automatically remove data not in the database (eg password_confirmation).
     *
     * @var boolean
     */
    public $autoPurgeRedundantAttributes = true;

    /**
     * Automatically create a hash for the password on save.
     *
     * @var boolean
     */
    public $autoHashPasswordAttributes = true;

    /**
     * Array of password Attributes.
     *
     * @var array
     */
    public static $passwordAttributes = array('password');

    /**
     * Rules for validation.
     *
     * @var array
     */
    public $create_rules = array(
        'email'    => 'required|email|unique:users',
        'name'     => 'required|alpha_dash|unique:users',
        'password' => 'required|min:6|confirmed',
    );
    public $update_rules = array(
        'email'    => 'required|email|unique:users',
        'name'     => 'required|alpha_dash|unique:users',
        'password' => 'min:6|confirmed',
    );
    public static $rules = array();

    /**
     * Groups relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany('Group');
    }

    /**
     * Is the user an admin?
     *
     * @return boolean
     */
    public function getIsAdminAttribute()
    {
        foreach ($this->groups as $group) {
            if ($group->is_admin) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return all users that are admins.
     *
     * @param LaravelBook\Ardent\Builder $builder
     * @returns LaravelBook\Ardent\Builder
     */
    public function scopeAdmins($builder)
    {
        return $builder->whereHas('groups', function($query) {
                /* @var $query LaravelBook\Ardent\Builder */
                $query->whereIsAdmin(true);
            });
    }

    /**
     * Return all users of a certain group.
     *
     * @param LaravelBook\Ardent\Builder $builder
     * @param string|array $group
     * @returns LaravelBook\Ardent\Builder
     */
    public function scopeGroup($builder, $group)
    {
        if (!is_array($group)) {
            $group = array($group);
        }
        return $builder->whereHas('groups', function($query) use($group) {
                /* @var $query LaravelBook\Ardent\Builder */
                $query->whereIn('name', $group);
            });
    }

}
