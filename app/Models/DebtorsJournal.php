<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** @mixin \Eloquent */
class DebtorsJournal extends Model
{
    /**
     * The table properties
     *
     * @var array
     */
    protected $table    = 'debtors_journal';
    protected $fillable = ['user_id','date','entry','amount','type'];
    public $timestamps  = false;

    /**
     * The user associated with the journal entry
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Return array of available entry descriptions
     *
     * @return array
     */
    public function availableEntries()
    {
        return [
            'Payment'               => 'Payment',
//            'Cancelled booking'     => 'Reverse shuttle charge',
            'Shuttle w/o booking'   => 'Add shuttle charge',
            'Duplicated payment'    => 'Reverse duplicated payment',
            'Miscl charge'          => 'Miscl charge',
            'Miscl credit'          => 'Miscl credit',
            'Write off'             => 'Write off',
        ];
    }

    /**
     * Array of entries which are credits (results in negative amount)
     *
     * @return array
     */
    public function creditEntries()
    {
        return [
            'Payment',
            'Cancelled booking',
            'Miscl credit',
            'Write off'
        ];
    }

    /**
     * Return journal entries not yet posted to statements
     *
     * @param null $customer
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function currentJournals($customer = null)
    {
        $invMonth = DebtorsStatement::invMonth();

        $journals = self::where('date', '>=', $invMonth->start)
            ->orderBy('date')
            ->get();

        // filter if customer specified
        if ( !is_null($customer) ) {
            $journals = $journals->filter(function ($item) use($customer) {
                return $item->user_id == $customer;
            });
        }

        return $journals;
    }
}
