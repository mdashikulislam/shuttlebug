<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** @mixin \Eloquent */
class Promotion extends Model
{
    /**
     * The table properties
     *
     * @var array
     */
    protected $table    = 'promotions';
    protected $fillable = ['type','view','name','description','rate','start','expire','restricted','restriction','list','hh'];
    protected $casts    = ['list' => 'array'];
    public $timestamps  = false;

    /**
     * Set the name
     *
     * @param string $value input
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower(trim($value)));
    }

    public static function isActive($name, $date)
    {
        $promo = Promotion::where('name', $name)
            ->where('start', '<=', $date)
            ->where('expire', '>=', $date)
            ->orderBy('expire')
            ->first();

        return is_null($promo) ? false : $promo;
    }

    /**
     * Return the ids of bookings that qualify for this promotion
     * Each promotion must be added to this method as well as the BookingPrice class
     *
     * @param $start
     * @param $end
     * @return array
     */
    public function morningMayhemBookings($start, $end)
    {
        $promo_suburbs = ['Hout Bay','Llandudno'];

        $bookings = Booking::with('puloc', 'doloc')->where('puloc_type', 'user')->where('doloc_type', 'school')->where('dotime', '<', '09:00:00')->whereBetween('date', [$start, $end])->get();

        foreach ( $bookings as $booking ) {
            if ( in_array($booking->puloc->suburb, $promo_suburbs) && in_array($booking->doloc->suburb, $promo_suburbs) ) {
                $ids[] = $booking->id;
            }
        }

        return $ids ?? [];
    }

    /**
     * Return the ids of bookings that qualify for this promotion
     * Each promotion must be added to this method as well as the BookingPrice class
     *
     * @param $start
     * @param $end
     * @return array
     */
    public function otherSuburbsBookings($start, $end)
    {
        $local_suburbs = ['Hout Bay', 'Llandudno'];
        $promo_suburbs = ['Clifton'];

        $bookings = Booking::with('puloc', 'doloc')->whereBetween('date', [$start, $end])->get();

        foreach ( $bookings as $booking ) {
            if ( (in_array($booking->puloc->suburb, $local_suburbs) && in_array($booking->doloc->suburb, $promo_suburbs)) ||
                (in_array($booking->puloc->suburb, $promo_suburbs) && in_array($booking->doloc->suburb, $local_suburbs)) ) {
                $ids[] = $booking->id;
            }
        }

        return $ids ?? [];
    }
}
