<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** @mixin \Eloquent */
class Guardian extends Model
{
    /**
     * The table properties
     *
     * @var array
     */
    protected $table    = 'guardians';
    protected $fillable = ['user_id','first_name','last_name','relation','phone','role'];
    public $timestamps  = false;

    /**
     * The user the guardians belong to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Set the First name
     *
     * @param string $value input
     */
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = formatName(trim($value));
    }

    /**
     * Set the Last name
     *
     * @param string $value input
     */
    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = formatName(trim($value));
    }

    /**
     * Set the Relation
     *
     * @param string $value input
     */
    public function setRelationAttribute($value)
    {
        $this->attributes['relation'] = ucfirst(strtolower(trim($value)));
    }

    /**
     * Set the Phone
     *
     * @param string $value input
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = formatPhone(trim($value));
    }

    /**
     * Get the full name
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }
}
