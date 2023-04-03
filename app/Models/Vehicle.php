<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** @mixin \Eloquent */
class Vehicle extends Model
{
    /**
     * The table properties
     *
     * @var array
     */
    protected $table    = 'vehicles';
    protected $fillable = ['model','seats','primary','geo','status'];
    public $timestamps  = false;

    /**
     * The vehicle's bookings
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany('App\Models\Booking');
    }

    /**
     * Set the Model
     *
     * @param string $value input
     */
    public function setModelAttribute($value)
    {
        $this->attributes['model'] = ucfirst(strtolower(trim($value)));
    }

    /**
     * Set the Reg
     *
     * @param string $value input
     */
//    public function setRegAttribute($value)
//    {
//        $this->attributes['reg'] = str_replace(' ','',strtoupper(trim($value)));
//    }

    /**
     * Get the model with reg
     *
     * @return string
     */
//    public function getModelRegAttribute()
//    {
//        return $this->model.' ('.$this->reg.')';
//    }

    /**
     * Get the model with reg
     *
     * @return string
     */
//    public function getDescriptionAttribute()
//    {
//        return $this->model.' ('.$this->seats.' seater)';
//    }

    /**
     * Return non-history drivers
     *
     * @return \Illuminate\Support\Collection
     */
//    public static function vehicleDrivers()
//    {
//        return DB::table('drivers')->select(DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id','first_name','last_name','from','to','status')->where('status', '<>', 'history')->orderBy('last_name')->get();
//    }

    /**
     * Set the driver attributes
     *
     * @param $request
     * @return mixed
     */
//    public function setDriverInput($request)
//    {
//        $input = $request->only('first_name','last_name','from','to','status');
//        $input['first_name'] = formatName(trim($input['first_name']));
//        $input['last_name'] = formatName(trim($input['last_name']));
//
//        return $input;
//    }

    /**
     * Find vehicles containing search term
     *
     * @param string    $find
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchVehicles($find)
    {
        $terms = str_replace(' ','%',$find);
        $term = '%'.$terms.'%';

        return self::where('status', '!=', 'history')->where(\DB::raw('CONCAT(id," ",model," ",seats," ",status)'), 'LIKE', $term)->get();
    }
}
