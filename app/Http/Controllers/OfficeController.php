<?php

namespace App\Http\Controllers;

use App\Http\Processors\SmsApi;
use App\Models\Booking;
use App\Models\PlanningReport;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


class OfficeController extends Controller
{
    /**
     * Display dashboard
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        $data = [
            'todo'      => $this->todoCta(),
            'trips'     => $this->tripsCta(),
            'prices'    => $this->pricesCta(),
            'sms'       => $this->smsCta()
        ];

        return view('office.dashboard', $data);
    }

    /**
     * Return the to-do cta
     *
     * @return mixed
     */
    private function todoCta()
    {
        $cta = null;

        // payments before invoice reminders
        if ( in_array(now()->day, [7,8,9]) ) {
            $cta['payments'] = (object) [
                'item' => 'Update debtors payments before reminder emails are sent on the 10th.',
                'link' => 'office/debtors/journal/create'
            ];
        }

        // payments before invoices
        if ( in_array(now()->day, [25,26,27]) ) {
            $cta['payments'] = (object) [
                'item' => 'Update debtors payments before invoices are sent on the 28th.',
                'link' => 'office/debtors/journal/create'
            ];
        }

        // new year holidays
        if ( now()->month >= 12 && is_null(Cache::get('holidays'.now()->addYear()->year)) ) {
            $cta['holidays'] = (object) [
                'item' => 'Public holidays and School terms for '.now()->addYear()->year.' need to be added. ',
                'link' => 'office/holidays'
            ];
        }

        return $cta;
    }

    /**
     * Return the trip planning cta
     *
     * @return mixed
     */
    private function tripsCta()
    {
        $cta = null;

        $plan_date = now()->dayOfWeek >= 5 ? new Carbon('next monday') : Carbon::tomorrow();
        $plan = PlanningReport::where('date', $plan_date)->first();
        $bookings = Booking::where('date', $plan_date)->count();
        $day = Carbon::parse($plan_date)->toDateString() == Carbon::tomorrow()->toDateString() ? 'tomorrow' : 'Monday';

        if ( is_null($plan) && $bookings > 0 ) {
            $cta = (object) [
                'item' => 'Trips for '.$day.' need to be planned.',
                'link' => 'office/operations/tripplans'
            ];
        }

        return $cta;
    }

    /**
     * Return the prices cta
     *
     * @return mixed
     */
    private function pricesCta()
    {
        $cta = null;
        $promotions = Promotion::whereBetween('expire', [now()->toDateString(), now()->addMonths(2)->toDateString()])->get();

        if ( count($promotions) > 0 ) {
            $list = '<ul>';
            foreach ( $promotions as $promo ) {
                $list .= '<li>'.$promo->name.' expires on '.$promo->expire.'</li>';
            }
            $list .= '</ul>';
            $cta[] = (object) [
                'item' => $list.'To revise promotions and special prices hit the button.',
                'link' => 'office/prices'
            ];
        }

        return $cta;
    }

    /**
     * Return the sms credit cta
     *
     * @return mixed
     */
    private function smsCta()
    {
        $cta = null;
        $expires = Carbon::today()->addHours(30);

        $credits = Cache::remember('credits', $expires, function () {
            $sms = new SmsApi();
            return (string) $sms->checkCredits();
        });

        if ( $credits < 200 ) {
            $cta = (object) [
                'item' => 'Less than 200 sms credits remaining. Place an order on smsPortal for 5001 credits.'
            ];
        }

        return $cta;
    }

    /**
     * Set a persistent filter
     *
     * @param string    $index
     * @param string    $filter
     * @param string    $value
     */
    public function setFilter($index, $filter, $value)              // user, suburb, Hout Bay
    {
        $filter_name = $index.'filter.';                            // userfilter.

        if ( $value == 'unset' ) {
            session()->forget($filter_name . $filter);              // userfilter.suburb
        } else {
            session()->put($filter_name . $filter, $value);         // userfilter.suburb = Hout Bay
        }

        return;
    }
}
