<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/04/26
 * Time: 10:33 AM
 */

/*
    |--------------------------------------------------------------------------
    | Price Updates
    |--------------------------------------------------------------------------
    |
    | Handles updates to booking prices when prices are changed.
    | Applies to standard prices, special prices and promotion prices.
    |
    */

namespace App\Http\Processors;


use App\Models\Booking;
use App\Models\Price;
use App\Models\Promotion;
use Carbon\Carbon;

class PriceUpdates
{
    /**
     * @var Booking
     * @var Price
     * @var Promotion
     */
    protected $booking;
    protected $price;
    protected $promotion;

    /**
     * PriceUpdates constructor.
     *
     * @param Booking $booking
     * @param Price $price
     * @param Promotion $promotion
     */
    public function __construct(Booking $booking, Price $price, Promotion $promotion)
    {
        $this->booking = $booking;
        $this->price = $price;
        $this->promotion = $promotion;
    }

    /**
     * Handle price updates for standard prices
     *
     * @param object        $new
     * @param bool          $edit
     * @param object|null   $current
     */
    public function standard($new, $edit, $current)
    {
        /**
         * New price
         * update current price to new price from date of new price
         */
        if ( !$edit ) {
            $current = $this->price->where('date', '<', $new->date)->orderBy('date', 'desc')->first();
            $this->booking->where('price', $current->basic_rate)->where('date', '>=', $new->date)->update(['price' => $new->basic_rate]);
        }

        /**
         * Edited price
         */
        if ( $edit ) {
            /**
             * Edited standard price
             * update current price to new price from date of current price
             */
            if ( $new->basic_rate != $current->basic_rate ) {
                $this->booking->where('price', $current->basic_rate)->where('date', '>=', $current->date)->update(['price' => $new->basic_rate]);
            }

            $ancestor = $this->price->where('date', '<', $new->date)->orderBy('date', 'desc')->first();

            /**
             * Edited start date to an earlier date
             * current price = new price if price not also changed, so work with new price
             * update ancestor price to new price from new date
             * if price changed also update current price to new price from new date
             */
            if ( $new->date < $current->date ) {
                if ( $new->basic_rate == $current->basic_rate ) {
                    $this->booking->where('date', '>=', $new->date)->where('price', $ancestor->basic_rate)->update(['price' => $new->basic_rate]);
                } else {
                    $this->booking->where('date', '>=', $new->date)->where('price', $ancestor->basic_rate)->orWhere('price', $current->basic_rate)->update(['price' => $new->basic_rate]);
                }

                /**
                 * Edited start date to a later date
                 * current price = new price if price not also changed, so work with new price
                 * update current price to ancestor price from current date up to new date
                 * the 'edited price' process has already revised prices from new date
                 */
            } elseif ( $new->date > $current->date ) {
                $this->booking->where('price', $new->basic_rate)->whereBetween('date', [$current->date, $new->date])->update(['price' => $ancestor->basic_rate]);
            }
        }

        return;
    }

    /**
     * Handle price updates for new special
     * change booking price to special price & add promo code
     *
     * @param object $new
     */
    public function newSpecial($new)
    {
        $method = $this->camelCase($new->name.'Bookings');

        // update price to new price from date of new price
        if ( method_exists($this->promotion, $method) ) {
            $bookings = $this->promotion->$method($new->start, $new->expire);
            $this->booking->whereIn('id', $bookings)->update(['price' => $new->rate, 'promo' => $new->id]);
        } else {
            dd('method not found');
        }

        return;
    }

    /**
     * Handle price updates for discontinued special
     * change booking prices to standard price after discontinue date
     * and remove promo code
     *
     * @param object        $new
     * @param object|null   $current
     */
    public function discontinueSpecial($new, $current)
    {
        $this->booking
            ->where('promo', $current->id)
            ->where('date', '>', $new->start)
            ->update(['price' => $new->rate, 'promo', 0]);

        return;
    }

    /**
     * Handle price updates for new future special
     * update booking price to new price from date of new price & add promo code
     *
     * @param object        $new
     */
    public function newFutureSpecial($new)
    {
        $ancestor = $this->promotion
            ->where('name', $new->name)
            ->where('start', '<', $new->start)
            ->orderBy('start', 'desc')
            ->first();

        if ( !is_null($ancestor) ) {
            $this->booking
                ->where('promo', $ancestor->promo)
                ->where('date', '>=', $new->start)
                ->update(['price' => $new->rate, 'promo' => $new->id]);
        }

        return;
    }

    /**
     * Handle price updates for edited future special
     * update bookings from future's previous price to future's new price from date of current start
     * promo id does not change
     *
     * @param $new
     * @param $current
     */
    public function editFutureSpecial($new, $current)
    {
        // first, if the price has changed, change current entry price to new price
        if ( $new->rate != $current->rate ) {
            $this->booking
                ->where('promo', $current->id)
                ->where('date', '>=', $current->start)
                ->update(['price' => $new->rate]);
        }

        /*
         * then, if the dates have changed the price of bookings between the changed dates must be revised.
         * if there is no previous ancestor (editing a new special) the bookings between changed start dates must be identified via promotion->method
         * else use the previous ancestor promo code
         */
        $ancestor = $this->promotion
            ->where('name', $new->name)
            ->where('id', '<>', $new->id)
            ->orderBy('start', 'desc')
            ->first();

        /**
         * Edited start date to an earlier date
         * bookings matching promo criteria between new start and current start or ancestor expiry
         */
        if ( $new->start < $current->start ) {
            if ( is_null($ancestor) ) {
                $method = $this->camelCase($new->name.'Bookings');
                if ( method_exists($this->promotion, $method) ) {
                    $bookings = $this->promotion->$method($new->start, $current->start);
                } else {
                    dd('method not found');
                }
            } else {
                $bookings = $this->booking
                    ->whereBetween('date', [$new->start, $ancestor->expire])
                    ->where('promo', $ancestor->id)
                    ->get()->pluck('id')->unique()->all();
            }
            $this->booking->whereIn('id', $bookings)->update(['price' => $new->rate, 'promo' => $new->id]);

        /**
         * Edited start date to a later date
         * bookings matching promo criteria between current start and new start or ancestor expiry and new start
         * reset to ancestor price or standard rate with matching promo code
         */
        } elseif ( $new->start > $current->start ) {
            if ( is_null($ancestor) ) {
                $method = $this->camelCase($new->name.'Bookings');
                if ( method_exists($this->promotion, $method) ) {
                    $bookings = $this->promotion->$method($current->start, Carbon::parse($new->start)->subDay()->toDateString());
                    $price = $this->price->where('date', '<=', $current->start)->orderBy('date', 'desc')->first()->basic_rate;
                    $promoid = 0;
                } else {
                    dd('method not found');
                }
            } else {
                $bookings = $this->booking
                    ->whereBetween('date', [$current->start, $new->start])
                    ->where('promo', $current->id)
                    ->get()->pluck('id')->unique()->all();
                $price = $ancestor->rate;
                $promoid = $ancestor->id;
            }
            $this->booking->whereIn('id', $bookings)->update(['price' => $price, 'promo' => $promoid]);
        }

        return;
    }

    /**
     * Handle price updates for promotions
     *
     * @param string        $name
     * @param object        $new
     * @param bool          $edit
     * @param object|null   $current
     */
    public function promotion($name, $new, $edit, $current)
    {
        $method = $this->camelCase($name.'Bookings');

        /**
         * New promotion
         * update price to new price from date of new price
         */
        if ( !$edit ) {
            $bookings = $this->promotion->$method($new->start, $new->expire);
            $this->booking->whereIn('id', $bookings)->update(['price' => $new->rate]);
        }

        /**
         * Edited promotion
         */
        if ( $edit ) {
            /**
             * Edited price
             * update current price to new price from date of current price
             */
            if ( $new->rate != $current->rate ) {
                $bookings = $this->promotion->$method($new->start, $new->expire);
                $this->booking->whereIn('id', $bookings)->update(['price' => $new->rate]);
            }

            /**
             * Edited dates
             * reset prices for current period to ancestor price ruling on current start date
             * update prices for new period to new price
             */
            if ( $new->start != $current->start || $new->expire != $current->expire ) {
                $ancestor = $this->price->where('date', '<=', $current->start)->orderBy('date', 'desc')->first();
                $bookings = $this->promotion->$method($current->start, $current->expire);
                $this->booking->whereIn('id', $bookings)->update(['price' => $ancestor->basic_rate]);

                $bookings = $this->promotion->$method($new->start, $new->expire);
                $this->booking->whereIn('id', $bookings)->update(['price' => $new->rate]);
            }
        }

        return;
    }

    /**
     * Convert promotion name into method name
     *
     * @param       $str
     * @param array $noStrip
     * @return mixed|null|string|string[]
     */
    private static function camelCase($str, array $noStrip = [])
    {
        // non-alpha and non-numeric characters become spaces
        $str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
        $str = trim($str);
        // uppercase the first character of each word
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);
        $str = lcfirst($str);

        return $str;
    }
}
