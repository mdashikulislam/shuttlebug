<?php

namespace App\Http\Controllers;

use App\Http\Processors\PriceUpdates;
use App\Models\Booking;
use App\Models\Price;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    /**
     * @var Price
     */
    protected $price;

    /**
     * PriceController constructor.
     *
     * @param Price $price
     */
    public function __construct(Price $price)
    {
        $this->price = $price;
    }

    /**
     * Display the prices web page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function webpage()
    {
        $data = [
            'prices'        => $this->price->rulingPrice(now()->toDateString()),
            'new_prices'    => $this->price->where('date', '>', now())->orderBy('date','desc')->first(),
            'specials'      => Promotion::where('type','spec')->where('expire', '>=', now())->where('view', 'pub')->get(),
            'promos'        => Promotion::where('type','promo')->where('start', '<=', now())->where('expire', '>=', now())->get(),
            'new_promos'    => Promotion::where('type','promo')->where('start', '>', now())->where('expire', '>=', now())->get(),
        ];

        return view('prices', $data);
    }

    /**
     * Display manage page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        $data = [
            'standard' => $this->price->where('date', '>', now())->orderBy('date','asc')->first(),
            'special' => Promotion::where('start', '>', now())->where('type', 'spec')->orderBy('start','asc')->get(),
            'promotion' => Promotion::where('start', '>', now())->where('type', 'promo')->orderBy('start','asc')->get()
        ];

        return view('office.prices.manage', $data);
    }

    /**
     * Display price listing
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function index()
    {
        $existing_prices = $this->price->where('date', '<=', now())->orderBy('date','desc')->get();
        $future_price = $this->price->where('date', '>', now())->orderBy('date','asc')->first();

        $data = [
            'existing'  => $existing_prices,
            'future'    => $future_price
        ];

        return view('office.prices.index', $data);
    }

    /**
     * Display form for creating or editing a price
     *
     * @param int/null   $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id = null)
    {
        $price = is_null($id) ? null : $this->price->where('date', '>', now())->orderBy('date','asc')->first();
        $current = $this->price->where('date', '<=', now())->orderBy('date','desc')->first();

        $data = [
            'price'     => $price,
            'current'   => $current
        ];

        return view('office.prices.form', $data);
    }

    /**
     * Store the price
     *
     * @param Request       $request
     * @param PriceUpdates  $process
     * @param int|null      $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, PriceUpdates $process, $id = null)
    {
        if ( $request->has('remove') ) {
            $this->delete($id);

            return redirect('office/prices/index')->with('confirm', 'Price has been removed');
        }

        if ( is_null($id) ) {
            $price = $this->price->create($request->all());

            // update effected booking prices
            $process->standard($price, false, null);

            return redirect('office/prices/edit/'.$price->id)->with('confirm', 'Price has been saved');
        } else {
            $price = $this->price->find($id);
            $current = clone $price;
            $price->update($request->all());

            // update effected booking prices if price changed
            $process->standard($price, true, $current);
        }

        return back()->with('confirm', 'Changes have been saved');
    }

    /**
     * Destroy the price
     * reset booking prices
     *
     * @param $id
     */
    public function delete($id)
    {
        // reset bookings
        $price = $this->price->find($id);
        $ancestor = $this->price->where('date', '<', $price->date)->orderBy('date', 'desc')->first();

        Booking::where('price', $price->basic_rate)->where('date', '>=', $price->date)->update(['price' => $ancestor->basic_rate]);

        // destroy price
        $price->delete();

        return;
    }
}
