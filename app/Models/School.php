<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/** @mixin \Eloquent */
class School extends Model
{
    /**
     * The table properties
     *
     * @var array
     */
    protected $table    = 'schools';
    protected $fillable = ['name','phone','dropfrom','dropby','unit','street','suburb','city','geo','status'];
    public $timestamps  = false;

    /**
     * The school's bookings
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany('App\Models\Booking', 'puloc_id');
    }

    /**
     * The school's children
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany('App\Models\Children');
    }

    /**
     * The school's pickup location
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function pickups()
    {
        return $this->morphMany('App\Models\Booking', 'puloc');
    }

    /**
     * The school's dropoff location
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function dropoffs()
    {
        return $this->morphMany('App\Models\Booking', 'doloc');
    }

    /**
     * Set the Name
     *
     * @param string $value input
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = formatName(trim($value));
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
     * Get the Venue
     *
     * @return string
     */
    public function getVenueAttribute()
    {
        return 'School';
    }

    /**
     * Find schools containing search term
     *
     * @param string    $find
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchSchools($find)
    {
        $terms = str_replace(' ','%',$find);
        $term = '%'.$terms.'%';

        return self::where(\DB::raw('CONCAT(name," ",suburb," ",city)'), 'LIKE', $term)->get();
    }

    /**
     * Return schools together with count of active children
     *
     * @param $q
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function schoolsWithChildrenCount($q)
    {
        $status = is_null($q) ? 'active' : 'inactive';

        return self::withCount(['children' => function($q) use($status) {
            $q->where('friend', '');
            $q->where('status', $status); }])
            ->where('name', '!=', 'None')->where('status', $status)->get();
    }

}
