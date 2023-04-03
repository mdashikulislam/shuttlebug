<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/** @mixin \Eloquent */
class EventBooking extends Model
{
    /**
     * The table properties
     *
     * @var array
     */
    protected $table    = 'event_bookings';
    protected $fillable = ['user_id','date','puloc','putime','doloc','dotime','passengers','tripfee','vehicle','driver','pugeo','dogeo'];
    public $timestamps  = true;

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * The user associated with the event booking
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Return list of customers who have event bookings this year
     *
     * @return array
     */
    public function customersWithCurrentBookings()
    {
        return self::with('user')
            ->whereYear('date', now()->year)
            ->groupBy('user_id')
            ->get()
            ->sortBy('user.alpha_name', SORT_NATURAL|SORT_FLAG_CASE)
            ->pluck('user.alpha_name','user.id')->all();
    }

    /**
     * Returns list of customers who have upcoming event bookings
     *
     * @return array
     */
    public function customersWithEmailableBookings()
    {
        return self::with('user')
            ->where('date', '>=', now()->toDateString())
            ->groupBy('user_id')
            ->get()
            ->sortBy('user.alpha_name', SORT_NATURAL|SORT_FLAG_CASE)
            ->pluck('user.alpha_name','user.id')->all();
    }

    /**
     * Return customer event bookings
     *  current bookings are bookings from today
     *  completed bookings are bookings from start of year up to yesterday
     *
     * @param $user_id
     * @param $q
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function customerBookings($user_id, $q)
    {
        if ( is_null($q) ) {
            $bookings = self::where('user_id', $user_id)
                ->where('date', '>', now()->subDay())
                ->orderBy('date')->orderBy('putime')->get();
        } else {
            $bookings = self::where('user_id', $user_id)
                ->where('date', '>', now()->startOfYear())
                ->where('date', '<=', now()->subDay())
                ->orderBy('date')->orderBy('putime')->get();
        }

        return $bookings;
    }

    /**
     * Compile available venues for the customer
     * adds previously booked venues to the standard venues of home, school & other
     *
     * @param $user
     * @return mixed
     */
    public function eventVenues($user)
    {
        $bookings = self::where('user_id', $user)
            ->whereYear('date', now()->year)
            ->orderBy('putime')
            ->get();

        $pups = $bookings->where('puloc', '!=', 'home')->pluck('puloc')->all();
        $dofs = $bookings->where('doloc', '!=', 'home')->pluck('doloc')->all();
        $locs = array_unique(array_merge($pups, $dofs));

        $venues['home'] = 'Home';
        foreach ( $locs as $loc ) {
            if ( substr($loc,0,2) == '70' ) {
                $venues[$loc] = School::find($loc)->name;
            } else {
                $venues[$loc] = $loc;
            }
        }
        $venues['school'] = 'A School';
        $venues['address'] = 'Other';

        return $venues;
    }

    /**
     * Return the ids of schools in puloc & doloc in given bookings
     *
     * @param $bookings
     * @return array
     */
    public function extractSchools($bookings)
    {
        $pups = collect($bookings)->where('puloc', '>', '700000')->where('puloc', '<', '800000')->pluck('puloc')->all();
        $dofs = collect($bookings)->where('doloc', '>', '700000')->where('doloc', '<', '800000')->pluck('doloc')->all();

        return array_unique(array_merge($pups, $dofs));
    }

    /**
     * Return upcoming event bookings for given customer
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function bookingsForEmail($id)
    {
        $bookings = self::where('user_id', '=', $id)
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('putime')
            ->get();

        // replace school ids with school name
        foreach ( $bookings as $booking ) {
            if ( substr($booking->puloc,0,2) == '70' ) {
                $booking->puloc = School::find($booking->puloc)->name;
            }
            if ( substr($booking->doloc,0,2) == '70' ) {
                $booking->puloc = School::find($booking->puloc)->name;
            }
        }

        return $bookings;
    }
}
