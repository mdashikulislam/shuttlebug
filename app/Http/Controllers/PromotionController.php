<?php

namespace App\Http\Controllers;

use App\Http\Processors\PriceUpdates;
use App\Models\Booking;
use App\Models\Price;
use App\Models\Promotion;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    /**
     * @var Promotion
     */
    protected $promotion;

    /**
     * PromotionController constructor.
     *
     * @param Promotion $promotion
     */
    public function __construct(Promotion $promotion)
    {
        $this->promotion = $promotion;
    }

    /**
     * Display special listing
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function index()
    {
        $existing = $this->promotion->where('start', '<=', now())->where('type', 'spec')->orderBy('expire','desc')->orderBy('start', 'desc')->get();
        $future = $this->promotion->where('start', '>', now())->where('type', 'spec')->orderBy('start','asc')->get();

        $data = [
            'existing'  => $existing,
            'future'    => $future
        ];

        return view('office.prices.special-index', $data);
    }

    /**
     * Display form for creating a special
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $futures = $this->promotion->where('type','spec')->where('start', '>', now())->get()->pluck('name')->all();
        $existing = $this->promotion->where('type','spec')->whereNotIn('name', $futures)->get()->pluck('name','name')->all();

        $data = [
            'existing' => $existing
        ];

        return view('office.prices.special-create-form', $data);
    }

    /**
     * Display form for editing a special
     *
     * @param int/null      $id
     * @param string/null   $name
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id, $name = null)
    {
        // webmaster creating a new special
        if ( is_null($id) && is_null($name ) ) {
            $special = null;
            $data = $this->newData();

        // editing special
        } elseif ( is_null($name) ) {
            $special = $this->promotion->find($id);
            $data = $this->editData($special);

        // adding a future special
        } else {
            $special = $this->promotion->where('name', $name)->orderBy('start', 'desc')->first();
            $data = $this->futureData($special);
        }

        $data['standard'] = Price::orderBy('date', 'desc')->first()->basic_rate;

        return view('office.prices.special-form', $data);
    }

    /**
     * Store the special
     *
     * @param Request $request
     * @param PriceUpdates $process
     * @param int|null    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, PriceUpdates $process, $id = null)
    {
        // remove future special
        if ( $request->has('remove') ) {
            $this->delete($id);

            return redirect('office/prices/special/index')->with('confirm', 'Special has been removed.');
        }

        // discontinue existing special
        if ( $request->has('stop') ) {
            $result = $this->discontinueExisting($request, $process, $id);

            if ( !$result ) {
                return back()->with('danger', 'Cannot be discontinued, there are future bookings.');
            }

            return back()->with('confirm', 'Special expiry date revised.');
        }

        // edit existing: update permitted fields
        if ( $request->edit == 'Edit Existing' ) {
            $special = $this->promotion->find($id);
            if ( $request->expire >= $special->expire ) {
                $input = $request->only('description', 'expire', 'view');
            } else {
                $input = $request->only('description', 'view');
            }
            $special->update($input);

            return back()->with('confirm', 'Changes have been saved');
        }

        // add expiry date if not provided
        if ( !$request->has('expire') || !$request->filled('expire') ) {
            $request->merge(['expire' => Carbon::parse($request->start)->addYear(1)->lastOfYear()->toDateString()]);
        }

        // add list if not provided
        if ( $request->restricted == 1 && !$request->has('list') ) {
            $request->merge(['list' => []]);
        }

        // adding new future
        if ( $request->edit == 'New Future' ) {
            $special = $this->promotion->create($request->all());
            $this->expireAncestor($special);
            $process->newFutureSpecial($special);

            return redirect('office/prices/special/edit/'.$special->id)->with('confirm', 'Future Special has been created');
        }

        // editing future:
        if ( $request->edit == 'Edit Future' ) {
            $special = $this->promotion->find($id);
            $current = clone $special;
            $special->update($request->all());

            // update effected booking prices
            $process->editFutureSpecial($special, $current);

            // update ancestor expiry if date changed
            if ( $special->start != $current->start ) {
                $this->expireAncestor($special, $current->start);
            }

            return back()->with('confirm', 'Changes have been saved');
        }

        // create a new special
        if ( is_null($id) ) {
            $special = $this->promotion->create($request->all());

            // update effected booking prices
            $process->newSpecial($special);

            return redirect('office/prices/special/edit/' . $special->id)->with('confirm', 'Price has been saved');
        }
    }

    /**
     * Destroy a future special
     * reset booking prices & remove promo code
     *
     * @param $id
     */
    public function delete($id)
    {
        // reset bookings
        $special = $this->promotion->find($id);
        $ancestor = $this->promotion->where('name', $special->name)->where('id', '!=', $special->id)->orderBy('start', 'desc')->first() ?? null;

        if ( is_null($ancestor) ) {
            // this is a new special therefore the previous price would be the standard price ruling at that date
            $ancestor = Price::where('date', '<=', $special->start)->orderBy('date', 'desc')->first();
            $ancestor->rate = $ancestor->basic_rate;
            $promo = 0;
        } else {
            // this is a future special so revert the ancestor expiry
            $ancestor->update(['expire' => Carbon::parse($ancestor->start)->addYear(1)->lastOfYear()->toDateString()]);
            $promo = $id;
        }

        Booking::where('promo', $id)->where('date', '>=', $special->start)->update(['price' => $ancestor->rate, 'promo' => $promo]);

        // destroy promotion
        $special->delete();

        return;
    }

    /**
     * Add expiry date to this special's ancestor
     *  the presence of old_start indicates a future special has been edited with a new start date
     *  so the expiry of its ancestor must be modified.
     *
     * @param string|null   $old_start
     * @param $special
     */
    private function expireAncestor($special, $old_start = null)
    {
        $expiry = Carbon::parse($special->start)->subDay()->toDateString();

        if ( is_null($old_start) ) {
            $this->promotion
                ->where('id', '<>', $special->id)
                ->where('name', $special->name)
                ->where('expire', '>=', now()->toDateString())
                ->update(['expire' => $expiry]);
        } else {
            $prev_expire = Carbon::parse($old_start)->subDay()->toDateString();
            $this->promotion
                ->where('id', '<>', $special->id)
                ->where('name', $special->name)
                ->where('expire', $prev_expire)
                ->update(['expire' => $expiry]);
        }

        return;
    }

    /**
     * Display promotions listing
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function promoIndex()
    {
        $existing = $this->promotion->where('start', '<=', now())->where('type', 'promo')->orderBy('start','desc')->get();
        $future = $this->promotion->where('start', '>', now())->where('type', 'promo')->orderBy('start','asc')->get();

        $data = [
            'existing'  => $existing,
            'future'    => $future
        ];

        return view('office.prices.promo-index', $data);
    }

    /**
     * Display form for creating or editing a promotion
     *
     * @param int/null   $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function promoEdit($id = null)
    {
        $promotion = is_null($id) ? null : $this->promotion->find($id);

        if ( !is_null($promotion) ) {
            $current = Price::where('date', '>=', $promotion->start)->first();
            if ( is_null($current) ) {
                $current = Price::where('date', '<', $promotion->start)->orderBy('date', 'desc')->first();
            }
        } else {
            $current = Price::orderBy('date', 'desc')->first();
            $futures = $this->promotion->where('type','promo')->where('start', '>', now())->get()->pluck('name')->all();
            $existing = $this->promotion->where('type','promo')->whereNotIn('name', $futures)->get()->pluck('description','name')->all();
        }

        $data = [
            'promotion' => $promotion,
            'current'   => $current,
            'existing'  => is_null($id) ? $existing : null
        ];

        return view('office.prices.promo-form', $data);
    }

    /**
     * Store the promotion
     *
     * @param Request $request
     * @param PriceUpdates $process
     * @param int|null    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function promoStore(Request $request, PriceUpdates $process, $id = null)
    {
        if ( $request->has('remove') ) {
            $this->promoDelete($id);

            return redirect('office/prices/promotion/index')->with('confirm', 'Promotion has been removed');
        }

        if ( is_null($id) ) {
            $promotion = $this->promotion->create($request->all());

            // update effected booking prices
            $process->promotion($request->name, $promotion, false, null);

            return redirect('office/prices/promotion/edit/'.$promotion->id)->with('confirm', 'Price has been saved');

        } else {
            $promotion = $this->promotion->find($id);
            $current = clone $promotion;
            $promotion->update($request->all());

            // update effected booking prices
            $process->promotion($request->name, $promotion, true, $current);
        }

        return back()->with('confirm', 'Changes have been saved');
    }

    /**
     * Destroy the promotion
     * reset booking prices
     *
     * @param $id
     */
    public function promoDelete($id)
    {
        // reset bookings
        $promotion = $this->promotion->find($id);
        $ancestor = Price::where('date', '<=', $promotion->start)->orderBy('date', 'desc')->first();

        Booking::where('price', $promotion->rate)->where('date', '>=', $promotion->start)->update(['price' => $ancestor->basic_rate]);

        // destroy promotion
        $promotion->delete();

        return;
    }

    /**
     * Return form data for new special
     *
     * @return array
     */
    private function newData()
    {
        $customers = User::active()->get();
        $schools = School::where('status', 'active')->where('name', '!=', 'None')->get();
        $suburbs = array_merge(collect($customers)->pluck('suburb','suburb')->all(), $schools->pluck('suburb','suburb')->all());
        ksort($suburbs);

        return [
            'promotion' => null,
            'edit'      => 'New',
            'current'   => Price::orderBy('date', 'desc')->first()->basic_rate,
            'customers' => collect($customers)->pluck('alphaname','id')->all(),
            'schools'   => $schools->pluck('name', 'id')->all(),
            'suburbs'   => $suburbs,

        ];
    }

    /**
     * Return form data for editing an existing special
     *
     * @param $special
     * @return array
     */
    private function editData($special)
    {
        $future = $special->start > now()->toDateString() ? true : false;
        if ( $special->restricted == 1 ) {
            $customers = $future ?
                User::active()->get() : User::whereIn('id', $special->list)->get();
            $schools = $future ?
                School::where('status', 'active')->where('name', '!=', 'None')->get() : School::whereIn('id', $special->list)->get();
            if ( $special->restriction == 'suburbs' ) {
                if ( $future ) {
                    $suburbs = array_merge($customers->pluck('suburb', 'suburb')->all(), $schools->pluck('suburb', 'suburb')->all());
                    ksort($suburbs);
                } else {
                    foreach ( $special->list as $suburb ) {
                        $suburbs[$suburb] = $suburb;
                    }
                }
            }
        }

        return [
            'promotion' => $special,
            'edit'      => $special->start > now()->toDateString() ? 'Edit Future' : 'Edit Existing',
            'customers' => $special->restriction == 'customers' ? $customers->pluck('alphaname','id')->all() : [],
            'schools'   => $special->restriction == 'schools' ? $schools->pluck('name','id')->all() : [],
            'suburbs'   => $special->restriction == 'suburbs' ? $suburbs : [],
        ];
    }

    /**
     * Return form data for a future special
     *
     * @param $special
     * @return array
     */
    private function futureData($special)
    {
        if ( $special->restriction == 'customers' ) {
            $customers = User::active()->get();

        } elseif ( $special->restriction == 'schools' ) {
            $schools = School::where('status', 'active')->where('name', '!=', 'None')->get();

        } elseif ( $special->restriction == 'suburbs' ) {
            $customers = User::active()->get();
            $schools = School::where('status', 'active')->where('name', '!=', 'None')->get();
            $suburbs = array_merge(collect($customers)->pluck('suburb','suburb')->all(), $schools->pluck('suburb','suburb')->all());
            ksort($suburbs);
        }

        return [
            'promotion' => $special,
            'edit'      => 'New Future',
            'current'   => Price::orderBy('date', 'desc')->first()->basic_rate,
            'customers' => $special->restriction == 'customers' ? collect($customers)->pluck('alphaname','id')->all() : [],
            'schools'   => $special->restriction == 'schools' ? collect($schools)->pluck('name', 'id')->all() : [],
            'suburbs'   => $special->restriction == 'suburbs' ? $suburbs : [],
        ];
    }

    /**
     * Update booking prices and revise expiry date
     *
     * @param \Illuminate\Http\Request          $request
     * @param \App\Http\Processors\PriceUpdates $process
     * @param                                   $id
     * @return bool
     */
    private function discontinueExisting(Request $request, PriceUpdates $process, $id)
    {
        $standard = Price::orderBy('date', 'desc')->first()->basic_rate;

        // reject if there are future bookings and special price is greater than standard price
        if ( $request->rate > $standard ) {
            if ( Booking::where('price', $request->rate)->where('date', '>', $request->discontinue)->count() > 0 ) {
                return false;
            }
        }

        $new = (object) ['rate' => $standard, 'start' => $request->discontinue];
        $current = (object) ['id' => $id, 'rate' => $request->rate];

        $process->discontinueSpecial($new, $current);
        $special = $this->promotion->find($id);
        $special->update(['expire' => $request->discontinue]);

        return true;
    }

}
