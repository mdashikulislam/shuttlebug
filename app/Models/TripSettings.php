<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/** @mixin \Eloquent */
class TripSettings extends Model
{
    /**
     * The table properties
     *
     * @todo add max # of 6 seaters
     * @todo revisit rest of settings
     * @var array
     */
    protected $table    = 'trip_settings';
    protected $fillable = ['fixed_schedule','fixed_venues','bus_route','trip_times','pref_am_vehicle','pref_pm_vehicle','passenger_limit','vehicle_wait','home_delay','school_dodelay','school_pudelay','buffer','pre_allocate'];
    public $timestamps  = false;
    protected $casts = [
        'fixed_venues' => 'array',
        'trip_times' => 'array',
        'school_pudelay' => 'array',
        'pre_allocate' => 'array'
    ];

    /**
     * Set the vehicle_wait period
     *
     * @param $value
     */
    public function setVehicleWaitAttribute($value)
    {
        $this->attributes['vehicle_wait'] = $value * 60;
    }

    /**
     * Set the home_delay period
     *
     * @param $value
     */
    public function setHomeDelayAttribute($value)
    {
        $this->attributes['home_delay'] = $value * 60;
    }

    /**
     * Set the school_dodelay period
     *
     * @param $value
     */
    public function setSchoolDodelayAttribute($value)
    {
        $this->attributes['school_dodelay'] = $value * 60;
    }

    /**
     * Set the buffer period
     *
     * @param $value
     */
    public function setBufferAttribute($value)
    {
        $this->attributes['buffer'] = $value * 60;
    }

    /**
     * Return passenger limits for venue/times
     *
     * @return array
     */
    public static function venueLimits()
    {
        return [
            700027  =>  ['12:00:00' => 2],  // bisschop
            700003  =>  ['12:20:00' => 1],  // valley
            700007  =>  ['12:30:00' => 5],  // montessori
            700002  =>  [                   // llandudno
                '12:45:00' => 3,
                '13:30:00' => 4,
                '14:30:00' => 4,
                '15:30:00' => 4,
            ],
            700008  =>  [                   // international
                '13:30:00' => 4,
                '14:30:00' => 4,
                '15:00:00' => 2,
                '15:30:00' => 4,
            ],
            700001  =>  [                   // kronendal
                '13:15:00' => 1,
                '14:00:00' => 2,
                '14:45:00' => 2,
            ],
        ];
    }
}
