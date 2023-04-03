<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;


/** @mixin \Eloquent */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The table properties
     *
     * @var array
     */
    protected $table    = 'users';
    protected $fillable = ['first_name','last_name','email','phone','mobile','role','relation','status','unit','street','suburb','city','geo','joindate','inv_email','inv_name','inv_adrs','password'];
    protected $hidden   = ['password', 'remember_token'];
    public $timestamps  = false;

    /**
     * The user's guardians
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function guardians()
    {
        return $this->hasMany('App\Models\Guardian');
    }

    /**
     * The user's children
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany('App\Models\Children');
    }

    /**
     * The user's xmurals
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function xmurals()
    {
        return $this->belongsToMany('App\Models\Xmural', 'user_xmurals');
    }

    /**
     * The user's bookings
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany('App\Models\Booking');
    }

    /**
     * The user's event bookings
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function eventbookings()
    {
        return $this->hasMany('App\Models\EventBooking');
    }

    /**
     * The user's pickup location
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function pickups()
    {
        return $this->morphMany('App\Models\Booking', 'puloc');
    }

    /**
     * The user's dropoff location
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function dropoffs()
    {
        return $this->morphMany('App\Models\Booking', 'doloc');
    }

    /**
     * Set the First name
     *
     * @param string $value input
     */
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = formatName(trim($value));
    }

    /**
     * Set the Last name
     *
     * @param string $value input
     */
    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = formatName(trim($value));
    }

    /**
     * Set the Relation
     *
     * @param string $value input
     */
    public function setRelationAttribute($value)
    {
        $this->attributes['relation'] = ucfirst(strtolower(trim($value)));
    }

    /**
     * Set the Email
     *
     * @param string $value input
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower(trim($value));
    }

    /**
     * Set the Password
     *
     * @param  string $value input
     */
    public function setPasswordAttribute($value)
    {
        if (strlen($value) < 40) {
            $this->attributes['password'] = bcrypt($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    /**
     * Set the Inv Email
     *
     * @param string $value input
     */
    public function setInvEmailAttribute($value)
    {
        $this->attributes['inv_email'] = strtolower(trim($value));
    }

    /**
     * Set the Inv Name
     *
     * @param string $value input
     */
    public function setInvNameAttribute($value)
    {
        $this->attributes['inv_name'] = ucwords(strtolower(trim($value)));
    }

    /**
     * Set the Inv Address
     *
     * @param string $value input
     */
    public function setInvAdrsAttribute($value)
    {
        $this->attributes['inv_adrs'] = ucwords(strtolower(trim($value)));
    }

    /**
     * Set the Mobile
     *
     * @param string $value input
     */
    public function setMobileAttribute($value)
    {
        $this->attributes['mobile'] = formatPhone(trim($value));
    }

    /**
     * Set the Phone
     *
     * @param string $value input
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = formatPhone(trim($value));
    }

    /**
     * Set the address Unit
     *
     * @param string $value input
     */
    public function setUnitAttribute($value)
    {
        $this->attributes['unit'] = ucwords(strtolower(trim($value)));
    }

    /**
     * Set the address Street
     *
     * @param string $value input
     */
    public function setStreetAttribute($value)
    {
        $this->attributes['street'] = ucwords(strtolower(trim($value)));
    }

    /**
     * Set the address Suburb
     *
     * @param string $value input
     */
    public function setSuburbAttribute($value)
    {
        $this->attributes['suburb'] = ucwords(strtolower(trim($value)));
    }

    /**
     * Set the address City
     *
     * @param string $value input
     */
    public function setCityAttribute($value)
    {
        $this->attributes['city'] = ucwords(strtolower(trim($value)));
    }

    /**
     * Set the Geo location
     *  format = -yy.yyyyyyy,xx.xxxxxxx
     *
     * @param $value
     */
    public function setGeoAttribute($value)
    {
        $this->attributes['geo'] = setLatLon($value);
    }

    /**
     * Get the Last_name, First_name
     *
     * @return string
     */
    public function getAlphaNameAttribute()
    {
        return $this->last_name.', '.$this->first_name;
    }

    /**
     * Get the full name
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Get the suburb (used to simulate the suburbs relation on schools & xmurals)
     *
     * @return array
     */
//    public function getSuburbsAttribute()
//    {
//        return (['name' => $this->suburb]);
//    }

    /**
     * Get the Address
     *
     * @return string
     */
    public function getAddressAttribute()
    {
        $unit = $this->unit > '' ? $this->unit.',' : '';

        return $unit.$this->street.','.$this->suburb.','.$this->city;
    }

    /**
     * Get the billing email
     *
     * @return string
     */
    public function getBillingEmailAttribute()
    {
        if ( $this->inv_email > '' ) {
            return $this->inv_email;
        }
        return $this->email;
    }

    /**
     * Get the billing name
     *
     * @return string
     */
    public function getBillingNameAttribute()
    {
        if ( $this->inv_name > '' ) {
            return $this->inv_name;
        }
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Get the billing address
     *
     * @return string
     */
    public function getBillingAdrsAttribute()
    {
        if ( $this->inv_adrs > '' ) {
            return $this->inv_adrs;
        }
        return $this->address;
    }

    /**
     * Get the Venue
     *
     * @return string
     */
    public function getVenueAttribute()
    {
        return 'Home';
    }

    /**
     * Return available admin relations
     *
     * @return array
     */
    public function adminRelations()
    {
        return [
            'system'    => 'System',
            'admin'     => 'Admin'
        ];
    }

    /**
     * Scope query to include active customers only
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('role', 'customer')->where('last_name', '>', '')->orderBy('last_name');
    }

    /**
     * Return customers with children
     *
     * @param $q
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function customersWithChildren($q)
    {
        $status = is_null($q) ? 'active' : 'inactive';

        return self::with(['children' => function($q) {
            $q->where('friend', ''); }])
            ->where('role', 'customer')
            ->where('status', $status)->get();
    }

    /**
     * Return prime customers
     *
     * @return array
     */
//    public static function primeCustomers()
//    {
//        return [
//            100692	=> "Lara Atkinson",
//            100120	=> "Katherina Bach",
//            100790	=> "Lynette Botha",
//            100698	=> "Elise Brunelle",
//            100189	=> "Kerry Cartwright",
//            100152	=> "Coleen Dreyer",
//            100779	=> "Melissa du Rand",
//            100607	=> "Camilla Fraser",
//            100849	=> "Preeta Govender",
//            100712	=> "Janke Jabbie",
//            100846	=> "Janet Malan",
//            100758	=> "Leanne Mostert",
//            100764	=> "Verena Staehle",
//            100483	=> "Rachelle Tilney"
//        ];
//    }

    /**
     * Return secondary customers
     *
     * @return array
     */
//    public static function secondaryCustomers()
//    {
//        return [
//            100695 => "Tanya Blacher",
//            100806 => "Bella Ellis",
//            100696 => "Samantha Gouws",
//            100709 => "Katherine Greig",
//            100149 => "Lisa Krohn",
//            100788 => "Liezel Matthee",
//            100154 => "Marie McGregor",
//            100293 => "Carrie Nixon",
//            100501 => "Jean Scheltema",
//            100704 => "Nadine Spencer-Derman"
//        ];
//    }

    /**
     * Find users containing search term
     *
     * @param string    $find
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchUsers($find)
    {
        $terms = str_replace(' ','%',$find);
        $term = '%'.$terms.'%';

        $users = self::where(\DB::raw('CONCAT(id," ",first_name," ",last_name," ",email," ",suburb)'), 'LIKE', $term)
            ->where('role', '!=', 'admin')
            ->get()->pluck('id','id')->all();

        $children = Children::where(\DB::raw('CONCAT(id," ",first_name," ",last_name)'), 'LIKE', $term)
            ->get()->pluck('user_id','user_id')->all();

        $ids = array_merge($users, $children);

        return self::with('children')->whereIn('id', $ids)->orderBy('last_name')->get();
    }

}
