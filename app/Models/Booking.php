<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;


/** @mixin \Eloquent */
class Booking extends Model
{
    /**
     * The table properties
     *
     * @var array
     */
    protected $table    = 'bookings';
    protected $fillable = ['user_id','passenger_id','date','puloc_id','putime','doloc_id','dotime','price','vehicle','promo','puloc_type','doloc_type','journal','fin_m'];
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
     * The user associated with the booking
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * The passenger associated with the booking
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function passenger()
    {
        return $this->belongsTo('App\Models\Children');
    }

    /**
     * The booking pickup location
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function puloc()
    {
        return $this->morphTo();
    }

    /**
     * The booking dropoff location
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function doloc()
    {
        return $this->morphTo();
    }

    /**
     * The vehicle associated with the booking
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle');
    }

    /**
     * Set the financial month when saving date
     *
     * @param string $value input
     */
    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value;
        if ( Carbon::parse($value)->day > 28 ) {
            $finm = Carbon::parse($value)->addMonthNoOverflow()->month;
        } else {
            $finm = Carbon::parse($value)->month;
        }
        $this->attributes['fin_m'] = $finm;
    }

    /**
     * Return the bookings for a given passenger on a given date
     *
     * @param $id
     * @param $date
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function passengerBookings($id, $date)
    {
        return self::where('passenger_id', $id)
            ->where('date', $date)
            ->where('journal', '')
            ->orderBy('putime')
            ->get();
    }

    /**
     * Return the bookings for a given passenger for given month
     *
     * @param $id
     * @param $month
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function monthBookings($id, $month)
    {
        return self::with('puloc','doloc')
            ->where('journal', '')
            ->where('date', 'like', $month.'%')
            ->where('passenger_id',$id)
            ->orderBy('date')
            ->orderBy('putime')
            ->get();
    }

    /**
     * Return list of customers who have bookings this year
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
     * Returns list of customers who have bookings in the next 2 weeks
     *
     * @return array
     */
    public function customersWithEmailableBookings()
    {
        return self::with('user')
            ->where('date', '>=', now()->toDateString())
            ->where('date', '<=', now()->addDays(14)->toDateString())
            ->groupBy('user_id')
            ->get()
            ->sortBy('user.alpha_name', SORT_NATURAL|SORT_FLAG_CASE)
            ->pluck('user.alpha_name','user.id')->all();
    }

    /**
     * Return bookings for given customer up to end of next week
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function bookingsForEmail($id)
    {

        return self::with('passenger','puloc','doloc')
            ->where('user_id', '=', $id)
            ->where('journal', '!=', 'cancelled')
            ->whereBetween('date', [now()->toDateString(), now()->addWeek()->endOfWeek()->toDateString()])
            ->orderBy('passenger_id')
            ->orderBy('date')
            ->orderBy('putime')
            ->get();
    }

    /**
     * Return bookings for given date
     *
     * @param $date
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function dailyBookings($date)
    {
        return self::with('passenger','puloc','doloc')
            ->where('date', $date)
            ->where('journal', '!=', 'cancelled')
            ->orderBy('putime')
            ->orderBy('dotime')
            ->orderBy('puloc_id')
            ->get();
    }

    /**
     * Return bookings for given passenger in week of given date
     *  This will include weekend bookings
     *
     * @param $passenger
     * @param $date
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    public function weekBookings($passenger, $date)
    {
        $dt = Carbon::createFromFormat('Y-m-d', $date);

        return self::with('puloc', 'doloc')
            ->where('passenger_id', $passenger)
            ->where('journal', '!=', 'cancelled')
            ->whereRaw('WEEKOFYEAR(date) = ?', [ $dt->weekOfYear])
            ->whereYear('date', $dt->year)
            ->orderBy('date')
            ->orderBy('putime')
            ->get();
    }

    /**
     * Return customer bookings
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
            $bookings = self::with('passenger:id,first_name,last_name', 'puloc', 'doloc')
                ->where('user_id', $user_id)
                ->where('date', '>', now()->subDay())
                ->orderBy('date')->orderBy('putime')->get();
        } else {
            $bookings = self::with('passenger:id,first_name,last_name', 'puloc', 'doloc')
                ->where('user_id', $user_id)
                ->where('date', '>', now()->startOfYear())
                ->where('date', '<=', now()->subDay())
                ->orderBy('date')->orderBy('putime')->get();
        }

        return $bookings;
    }

    /**
     * Return warnings for full venues
     * this is obsolete
     *
     * @param $date
     * @param $settings
     * @return mixed
     */
//    public function bookingWarnings($date, $settings)
//    {
//        $locs = [];
//
//        $bookings = self::with('puloc')
//            ->where('date', $date)
//            ->where('journal', '!=', 'cancelled')
//            ->whereBetween('putime', ['09:00:00','15:59:00'])
//            ->orderBy('putime')
//            ->get();
//
//        $timeslots = $bookings->pluck('putime')->unique()->all();
//
//        if ( $settings->fixed_schedule ) {
//            // return full venues
//            $venues = $bookings->pluck('puloc_id')->unique()->all();
//            foreach ( $venues as $venue ) {
//                foreach ( $timeslots as $time ) {
//                    $lifts = $bookings->where('putime', $time)->where('puloc_id', $venue)->all();
//                    if ( count($lifts) > 0 ) {
//                        $limit = $time > '11:59:00' && $time < '16:00:00' ? collect($settings->fixed_venues)->where('loc', $venue)->where('time', substr($time, 0, 5))->first() : ['limit' => 4];
//                        if ( count($lifts) >= $limit['limit'] ) {
//                            $locs[$time][] = collect($lifts)->first()->puloc->name;
//                        }
//                    }
//                }
//            }
//
//        } else {
//            // return times where number of venues = number vehicles
//            foreach ( $timeslots as $time ) {
//                $locs[$time] = $bookings->where('putime', $time)->pluck('puloc.name')->unique()->values()->all();
//            }
//
//            $vehicles = Vehicle::where('status', 'active')->count();
//            foreach ( $locs as $time => $array ) {
//                if ( count($array) < $vehicles ) {
//                    unset($locs[$time]);
//                }
//            }
//        }
//
//        return $locs;
//    }

    /**
     * Check booking can be priced
     *
     * @param array $item
     * @param string $date
     * @param string $putype
     * @param string $dotype
     * @return bool
     */
    public static function validBooking($item, $date, $putype, $dotype)
    {
        $model = 'App\Models\\' . ucfirst($putype);
        $pup = $model::find($item['puloc_id']);
        $model = 'App\Models\\' . ucfirst($dotype);
        $dof = $model::find($item['doloc_id']);

        $local = ['Hout Bay', 'Llandudno'];
        if ( in_array($pup->suburb, $local) && in_array($dof->suburb, $local) ) {
            return true;
        }

        $promotions = Promotion::where('start', '<=', $date)->where('expire', '>=', $date)->get();
        foreach ( $promotions as $promotion ) {
            if ( $promotion->restriction == 'suburbs' ) {
                // validation for southern suburbs promotion
                if ( $promotion->name == 'Southern Suburbs' ) {
                    if ( in_array($pup->suburb, $promotion->list) && in_array($dof->suburb, $promotion->list) ) {
                        return true;
                        break;
                    }
                }
                else {
                    // vaidation for other suburb promotions
                    if ( (in_array($pup->suburb, $local) && in_array($dof->suburb, $promotion->list)) ||
                        (in_array($dof->suburb, $local) && in_array($pup->suburb, $promotion->list)) ) {
                        return true;
                        break;
                    }
                }

            } elseif ( $promotion->restriction == 'schools' ) {
                if ( (in_array($pup->suburb, $local) && in_array($dof->id, $promotion->list)) ||
                    (in_array($dof->suburb, $local) && in_array($pup->id, $promotion->list)) ) {
                    return true;
                    break;
                }
            }
        }

        return false;
    }
}
