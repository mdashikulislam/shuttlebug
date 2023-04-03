<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;

/** @mixin \Eloquent */
class Children extends Model
{
    /**
     * The table properties
     *
     * @var array
     */
    protected $table    = 'children';
    protected $fillable = ['user_id','school_id','first_name','last_name','dob','gender','phone','friend','medical','status'];
    public $timestamps  = false;

    /**
     * The user associated with the children
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * The school associated with the children
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo('App\Models\School');
    }

    /**
     * The childrens bookings
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany('App\Models\Booking', 'passenger_id');
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
     * Get the full name
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Get the age
     *
     * @return string
     */
    public function getAgeAttribute()
    {
        if ( $this->dob == '0000-00-00' ) {
            return '?';
        } else {
            $d1 = new DateTime($this->dob);
            $d2 = new DateTime();
            $diff = $d2->diff($d1);

            return $diff->y;
        }
    }

    /**
     * Return the name with status
     *
     * @return string
     */
    public function getNameWithStatusAttribute()
    {
        $status = $this->status == 'inactive' ? ' ( inactive )' : '';
        return $this->first_name.' '.$this->last_name.$status;
    }

    /**
     * Return the active children for given parent
     *
     * @param $user_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function parentsActiveChildren($user_id)
    {
        return self::where('user_id', $user_id)
            ->where('status', 'active')
            ->get()
            ->sortBy('name')->sortBy('friend');
    }

    /**
     * Return listing of children with different last names to their parent
     * for given collection of parents
     *
     * @param $users
     * @return array
     */
    public static function childrenWithDifferentNames($users)
    {
        $children = [];

        foreach ( $users as $user ) {
            foreach ( $user->children as $child ) {
                if ( $child->friend == '' && $child->last_name != $user->last_name ) {
                    $children[$user->id] = $child->last_name;
                }
            }
            natcasesort($children);
        }

        return $children;
    }
}
