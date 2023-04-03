<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** @mixin \Eloquent */
class Price extends Model
{
    /**
     * The table properties
     *
     * @var array
     */
    protected $table    = 'prices';
    protected $fillable = ['date','basic_rate','sibling_disc','volume_disc','hh'];
    public $timestamps  = false;

    /**
     * Return prices ruling on given date
     *
     * @param $date
     * @return Model|null|object|static
     */
    public static function rulingPrice($date)
    {
        return self::where('date', '<=', $date)
            ->orderBy('date','desc')
            ->first();
    }
}
