<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** @mixin \Eloquent */
class Xmural extends Model
{
    protected $table    = 'xmurals';
    protected $fillable = ['venue', 'unit', 'street', 'suburb', 'city', 'view', 'time', 'geo'];
    public $timestamps  = false;

    /**
     * The xmurals belonging to the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function user()
    {
        return $this->belongsToMany('App\Models\User', 'user_xmurals');
    }

    /**
     * The xmural's pickup location
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function pickups()
    {
        return $this->morphMany('App\Models\Booking', 'puloc');
    }

    /**
     * The xmural's dropoff location
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function dropoffs()
    {
        return $this->morphMany('App\Models\Booking', 'doloc');
    }

    /**
     * Set the Venue
     *
     * @param string $value input
     */
    public function setVenueAttribute($value)
    {
        $this->attributes['venue'] = formatName(trim($value));
    }

    /**
     * Set the address Unit
     *
     * @param string $value input
     */
    public function setUnitAttribute($value)
    {
        $this->attributes['unit'] = ucwords(strtolower(trim($value)));
    }

    /**
     * Set the address Street
     *
     * @param string $value input
     */
    public function setStreetAttribute($value)
    {
        $this->attributes['street'] = ucwords(strtolower(trim($value)));
    }

    /**
     * Set the address Suburb
     *
     * @param string $value input
     */
    public function setSuburbAttribute($value)
    {
        $this->attributes['suburb'] = ucwords(strtolower(trim($value)));
    }

    /**
     * Set the address City
     *
     * @param string $value input
     */
    public function setCityAttribute($value)
    {
        $this->attributes['city'] = ucwords(strtolower(trim($value)));
    }

    /**
     * Set the Geo location
     *  format = -yy.yyyyyyy,xx.xxxxxxx
     *
     * @param $value
     */
    public function setGeoAttribute($value)
    {
        $this->attributes['geo'] = setLatLon($value);
    }

    /**
     * Get the xmural name (alias for venue)
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->venue;
    }

    /**
     * Get the venue with street and suburb
     *
     * @return string
     */
    public function getVenueLocationAttribute()
    {
        return $this->venue.' : '.$this->street.','.$this->suburb;
    }

    /**
     * Get the Address
     *
     * @return string
     */
    public function getAddressAttribute()
    {
        $unit = $this->unit > '' ? $this->unit.',' : '';

        return $unit.$this->street.','.$this->suburb.','.$this->city;
    }

    /**
     * Return xmurals that are time critical
     *
     * @return array
     */
    public static function timeCriticalXmurals()
    {
        return self::where('time', 1)
            ->get()
            ->pluck('venue')->all();
    }

}
