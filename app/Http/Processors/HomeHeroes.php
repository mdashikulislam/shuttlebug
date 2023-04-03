<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2019/01/06
 * Time: 8:27 AM
 */

namespace App\Http\Processors;


use App\Models\Booking;
use App\Models\Price;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class HomeHeroes
{

    /**
     * Return the start and end dates of the requested week
     *
     * @param $date
     * @return string
     */
    public function getDate($date)
    {
        if ( is_null($date) ) {
            if ( now()->isWeekday() ) {
                $date = now()->toDateString();
            } else {
                $date = Carbon::parse('next monday')->toDateString();
            }
        }

        return $date;
    }

    /**
     * @param $date
     * @param $plan
     * @return array
     */
    public function buildData($date, $plan)
    {
        $times = $this->planTimes($plan);
        $vehicles = $this->planVehicles($plan);
        $vehicles = $this->planMileage($date, $vehicles);

        return [$times, $vehicles];
    }

    /**
     * Return the homeheroes invoice for the given date
     *
     * @param $date
     * @return mixed
     */
    public function getInvoice($date)
    {
        // pull invoice from statement if requested date older than 15 days
        if ( Carbon::parse($date) < now()->subDays(15)->toDateString() ) {
            $statement = DB::table('homeheroes_statement')->where('date', $date)->get();
            foreach ( $statement as $line ) {
                $invoice[] = [
                    'price' => $line->price,
                    'pass'  => $line->price == 0 ? $line->passengers.' noshow' : $line->passengers,
                    'value' => $line->value
                ];
            }

            return $invoice ?? [];
        }

        // build invoice from bookings
//        if ( Carbon::parse($date) < now()->toDateString() ) {
        $noshows = DB::table('ts_feedback')->where('date', $date)->where('data', 'noshow')->where('vehicle', '>', 102)->get()->pluck('booking_id')->all();

        $selects = ['promo','price','COUNT(*) as bookings'];
        $lifts = Booking::selectRaw(implode(',', $selects))
            ->where('date', $date)
            ->where('vehicle', '>', 102)
            ->whereNotIn('id', $noshows)
            ->groupBy('price')
            ->get();

        $std = Price::rulingPrice($date)->hh;
        $lines = [];

        foreach ( $lifts as $lift ) {
            if ( $lift->promo == 0 ) {
                $lines[$std] = isset($lines[$std]) ? $lines[$std] + $lift->bookings : $lift->bookings;
            } else {
                $cp = Promotion::find($lift->promo)->hh;
                if ( $cp > 0 ) {
                    $lines[$cp] = isset($lines[$cp]) ? $lines[$cp] + $lift->bookings : $lift->bookings;
                } else {
                    $lines[$std] = isset($lines[$std]) ? $lines[$std] + $lift->bookings : $lift->bookings;
                }
            }
        }

        foreach ( $lines as $cost => $pass ) {
            $invoice[] = [
                'price' => $cost,
                'pass'  => $pass,
                'value' => (int) ($pass * $cost)
            ];
        }

        if ( count($noshows) > 0 ) {
            $invoice[] = [
                'price' => 0,
                'pass'  => count($noshows).' noshow',
                'value' => 0
            ];
        }

        // add invoice to statement if new or changed
        if ( isset($invoice) ) {
            $statement = DB::table('homeheroes_statement')->where('date', $date)->sum('value');
            if ( $statement != array_sum(array_column($invoice, 'value')) ) {
                DB::table('homeheroes_statement')->where('date', $date)->delete();
                foreach ( $invoice as $line ) {
                    $input['date'] = $date;
                    $input['passengers'] = str_replace(' noshow', '', $line['pass']);
                    $input['price'] = $line['price'];
                    $input['value'] = $line['value'];
                    DB::table('homeheroes_statement')->insert($input);
                }
            }
        } else {
            DB::table('homeheroes_statement')->where('date', $date)->delete();
        }

//            return $invoice ?? null;
//        }

        if ( Carbon::parse($date) < now()->toDateString() ) {
            return $invoice ?? null;
        }

        return null;
    }

    /**
     * Extract unique pickup times from plan
     *
     * @param $plan
     * @return array
     */
    private function planTimes($plan)
    {
        $am_times = $day_times = [];

        if ( !is_null($plan->am) ) {
            foreach ( $plan->am as $vehicle => $route ) {
                if ( $vehicle != 102 ) {
                    $am_times[] = collect($route)->where('type', 'pickup')->pluck('putime')->unique()->all();
                }
            }
        }
        $am_times = Arr::flatten($am_times);

        if ( !is_null($plan->day) ) {
            foreach ( $plan->day as $pickup ) {
                if ( count($pickup['free']) > 1 ||  (count($pickup['free']) == 1 && !isset($pickup['free'][102])) ) {
                    $day_times[] = $pickup['pickup']['time'];
                }
            }
        }

        $times = array_unique(array_merge($am_times, $day_times));
        sort($times);

        return $times;
    }

    /**
     * Return the vehicles with attendants for the plan
     *
     * @param $plan
     * @return array
     */
    private function planVehicles($plan)
    {
        $vehicles = [];

        if ( !is_null($plan->am_vehicles) ) {
//            $attendants = DB::table('attendants')->where('status', 'active')->where('from', '<', '09:00:00')->get();
//            $used = ['Lyn'];

            foreach ( $plan->am_vehicles as $vehicle => $data ) {
//                $attendant = collect($attendants)->whereNotIn('first_name', $used)->first()->first_name ?? 'A N Other';
                if ( $vehicle != 102 ) {
                    $vehicles[$vehicle] = [
                        'id'        => $vehicle,
                        'seats'     => $data['seats'],
                        'pax'       => $data['pass'],
                        'att'       => $data['att'],
                        'times'     => collect($plan->am[$vehicle])->where('type', 'pickup')->pluck('venue', 'putime')->all(),
//                        'am_attendant'  => $attendant,
                    ];
//                    $used[] = $attendant;
                }
            }
        }

        if ( !is_null($plan->day_vehicles) ) {
//            $attendants = $this->attendantVehicles(array_keys($plan->day_vehicles));

            foreach ( $plan->day_vehicles as $vehicle => $data ) {
                $vplan = collect($plan->day)->filter(function ($item) use ($vehicle) {
                    return isset($item['free'][$vehicle]);
                });

                if ( $vehicle != 102 && $data['pass'] > 0 ) {
                    if ( isset($vehicles[$vehicle]) ) {
                        $vehicles[$vehicle]['seats'] = $data['seats'] > 0 ? $data['seats'] : $vehicles[$vehicle]['seats'];
                        $vehicles[$vehicle]['pax'] = $vehicles[$vehicle]['pax'] + $data['pass'];
                        $vehicles[$vehicle]['att'] = $data['att'];
                        $vehicles[$vehicle]['times'] = $vehicles[$vehicle]['times'] + collect($vplan)->pluck('pickup.venue', 'pickup.time')->all();
//                        $vehicles[$vehicle]['day_attendant'] = collect($attendants)->where('vehicle', $vehicle)->first()->first_name;
                    } else {
                        $vehicles[$vehicle] = [
                            'id'        => $vehicle,
                            'seats'     => $data['seats'],
                            'pax'       => $data['pass'],
                            'att'       => $data['att'],
                            'times'         => collect($vplan)->pluck('pickup.venue', 'pickup.time')->all(),
//                            'am_attendant'  => null,
//                            'day_attendant' => collect($attendants)->where('vehicle', $vehicle)->first()->first_name,
                        ];
                    }
                }
            }
        }
        ksort($vehicles);

        return $vehicles;
    }

    /**
     * Allocate vehicles to attendants
     *
     * @param $vids
     * @return array
     */
//    private function attendantVehicles($vids)
//    {
//        $attendants = DB::table('attendants')->where('status', 'active')->where('to', '>', '09:00:00')->get();
//
//        collect($attendants)->where('id', 1002)->first()->vehicle = in_array(102, $vids) ? 102 : null;
//
//        $used[] = 102;
//        foreach( $vids as $ix => $vid ) {
//            if ( $ix < count($attendants) && !in_array($vid, $used) ) {
//                foreach ( $attendants as $attendant ) {
//                    if ( $attendant->id != 1002 && !isset($attendant->vehicle) ) {
//                        $attendant->vehicle = $vid;
//                        $used[] = $vid;
//                        break;
//                    }
//                }
//            } elseif ( !in_array($vid, $used) ) {
//                $attendants[] = (object) [
//                    "id"            => collect($attendants)->last()->id + 1,
//                    "first_name"    => "A N Other",
//                    "last_name"     => "",
//                    "status"        => "active",
//                    "vehicle"       => $vid,
//                ];
//            }
//        }
//
//        return $attendants;
//    }

    /**
     * Put shuttle mileages onto vehicles array
     *
     * @param       $date
     * @param array $vehicles
     * @return array
     */
    private function planMileage($date, $vehicles)
    {
        $vids = array_keys($vehicles);

        $selects = ['reg','date','SUM(km) as km'];
        $mileages = DB::table('_log_shuttle_mileage')->selectRaw(implode(',', $selects))
            ->where('date', $date)
            ->whereIn('reg', $vids)
            ->orderBy('id', 'desc')
            ->groupBy('reg')
            ->get();

        foreach ( $vehicles as $id => $vehicle ) {
            $vehicles[$id]['mileage'] = $mileages->where('reg', $id)->first()->km;
        }

        return $vehicles;
    }
}
