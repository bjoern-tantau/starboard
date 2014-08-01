<?php

class Group extends Base
{
    /**
     * Rules for validation.
     */
    public static $rules = array(
        'name'     => 'required|alpha_dash|unique:groups',
    );

    /**
     *  Attributes available for mass-filling.
     *
     * @var array
     */
    protected $fillable = array('name', 'is_admin');

    /**
     * Users relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('User');
    }

}
