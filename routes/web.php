<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**
 * landing pages
 */
Route::get('/', function () {
	return view('home');
});
Route::get('test','TestController@index');
/**
 * authentication
 */
Auth::routes();
Route::get('auth/registered',           function () { return view('auth/registered'); });

/**
 * website pages
 */
Route::get('contact',                   function () { return view('contact'); });
Route::get('areas',                     function () { return view('areas'); });
Route::get('schools',                   'SchoolController@webpage');
Route::get('staff',                     function () { return view('staff'); });
Route::get('safety',                    function () { return view('safety'); });
Route::get('faq',                       function () { return view('faq'); });
Route::get('prices',                    'PriceController@webpage');
Route::get('privacy',                   function () { return view('privacy'); });
Route::get('terms',                     function () { return view('terms'); });
Route::get('homeheroes/{d?}',           'TripSheetController@hhschedule');
Route::get('hh/statement/{w?}',         'TripSheetController@hhstatement');

/**
 * account pages
 */
Route::middleware(['auth'])->prefix('myaccount')->group(function() {
    // profile
    Route::get('profile/{id}',                  'UsersController@edit');
    Route::post('profile/update/{id}',          'UsersController@update');
    Route::get('password',                      'MyAccountController@password');
    Route::post('password/update/{id}',         'MyAccountController@passwordUpdate');
    Route::get('account',                       function () { return view('myaccount.account'); });
    Route::get('account/{id}',                  'MyAccountController@close');

    // guardians
    Route::get('guardians/{id}',                'GuardianController@edit');
    Route::post('guardians/store',              'GuardianController@store');

    // children
    Route::get('children/{id}',                 'ChildrenController@show');
    Route::get('form/{id}/{child}',             'ChildrenController@form');
    Route::post('children/store/',              'ChildrenController@store');
    Route::post('children/update/{id}',         'ChildrenController@update');

    // xmurals
    Route::get('xmurals/{id}',                  'XmuralController@show');
    Route::get('xmurals/form/{id}/{xmural}',    'XmuralController@form');
    Route::post('xmurals/store/',               'XmuralController@store');
    Route::post('xmurals/update/{id}',          'XmuralController@update');

    // bookings
    Route::get('bookings/{id}',                 'BookingController@customerIndex');

    // billing
    Route::get('billing/{id}',                  'DebtorsController@showFinancials');
    Route::get('billing/invoice/{id}/{date}',   'DebtorsController@getInvoice');
});

// trip sheets
Route::prefix('tripsheets')->group(function() {
    Route::get('/',                             'TripSheetController@showLogin');
    Route::post('triplog',                      'TripSheetController@login');
    Route::get('tripsheet/{id}',                'TripSheetController@tripsheet');
    Route::get('profile/{name}/{time}',         'TripSheetController@profile');
    Route::get('signature/{name}/{time}',       'TripSheetController@signature');
    Route::post('mapdata',                      'TripSheetController@mapdata');
    Route::post('feedback',                     'TripSheetController@feedback');
});

/**
 * Office pages
 */
Route::middleware(['auth', 'can:enterOffice'])->prefix('office')->group(function() {
    Route::get('/',                                 'OfficeController@show');
    Route::get('/analytics',                        'StatsController@show');
    Route::get('/analytics/financials',             'StatsController@financials');
    Route::get('/analytics/trips',                  'StatsController@trips');
    Route::get('/analytics/customers',              'StatsController@customers');
    Route::get('/analytics/vehicles',               'StatsController@vehicles');
    Route::get('/analytics/history',                'StatsController@history');
    Route::get('/analytics/schools',                'StatsController@schools');
    Route::get('/analytics/proposal',               'StatsController@proposal');
    Route::get('testing',                           'TestingController@show');
    Route::get('setfilter/{index}/{filter}/{value}','OfficeController@setfilter');

    // bookings
    Route::prefix('bookings')->group(function() {
        Route::get('/',                             'BookingController@show');
        Route::get('list/daily/{date}',             'BookingController@dailyIndex');
        Route::get('list/customer/{id}/{q?}',       'BookingController@customerIndex');
        Route::get('edit/{id}/{date?}',             'BookingController@edit');
        Route::post('store',                        'BookingController@store');
        Route::post('cancel',                       'BookingController@destroy');
        Route::get('duplicate/{id}/{date?}',        'BookingController@showDuplicate');
        Route::post('duplicate/store',              'BookingController@storeDuplicate');
        Route::get('email/{id}/{review?}',          'BookingController@emailBookings');
    });

    // event bookings
    Route::prefix('events')->group(function() {
        Route::get('/',                             'EventBookingController@show');
        Route::get('list/customer/{id}/{q?}',       'EventBookingController@customerIndex');
        Route::get('edit/{id}/{date?}',             'EventBookingController@edit');
        Route::post('store',                        'EventBookingController@store');
        Route::post('cancel',                       'EventBookingController@destroy');
        Route::get('email/{id}/{review?}',          'EventBookingController@emailBookings');
    });

    // debtors
    Route::prefix('debtors')->group(function() {
        Route::get('/',                             'DebtorsController@show');
        Route::get('journal/create',                'DebtorsController@createJournal');
        Route::post('journal/store',                'DebtorsController@storeJournal');
        Route::get('balance/{id}',                  'DebtorsController@getLatestBalance');
        Route::get('financials/{id}',               'DebtorsController@showFinancials');
        Route::get('invoice/{id}/{date}',           'DebtorsController@getInvoice');
        Route::get('outstanding/{date?}',           'DebtorsController@showOutstanding');
        Route::get('pdf/{doc}/{id}/{date?}',        'DebtorsController@customerPdf');
        Route::get('update/{date}',                 'DebtorsController@updateStatement');
        Route::get('deliveries/{id}/{month}',       'DebtorsController@deliveries');
        Route::get('emergency',                     'DebtorsController@emergencyMonthEnd');
    });

    // holidays
    Route::prefix('holidays')->group(function() {
        Route::get('/',                             'HolidayController@show');
        Route::get('edit/{year?}',                  'HolidayController@edit');
        Route::post('store',                        'HolidayController@store');
        Route::get('public/edit/{year?}',           'HolidayController@editPublic');
        Route::post('public/store',                 'HolidayController@storePublic');
    });

    // operations
    Route::prefix('operations')->group(function() {

        // trip planning
        Route::prefix('tripplans')->group(function() {
            Route::get('/',                         'TripPlanController@show');
            Route::get('settings',                  'TripPlanController@settings');
            Route::post('update/{id}',              'TripPlanController@updateSettings');
            Route::any('plan',                      'TripPlanController@showPlan');
            Route::get('plan/build',                'TripPlanController@buildPlan');
            Route::post('hack',                     'TripPlanController@updatePlan');
            Route::get('plan/rerun/{data?}',        'TripPlanController@rerunPlan');
            Route::get('plan/amrerun',              'TripPlanController@rerunAmPlan');
        });

        // trip sheets
        Route::prefix('tripsheets')->group(function() {
            Route::get('/',                         'TripSheetController@show');
            Route::post('summary',                  'TripSheetController@summary');
        });

        // vehicles
        Route::prefix('vehicles')->group(function() {
            Route::get('/',                         'VehicleController@show');
            Route::get('index',                     'VehicleController@index');
            Route::get('create',                    'VehicleController@create');
            Route::get('edit/{id}',                 'VehicleController@edit');
            Route::post('store',                    'VehicleController@store');
            Route::post('update/{id}',              'VehicleController@update');
            Route::get('find/{string}',             'VehicleController@search');
        });

        // attendants
        Route::prefix('attendants')->group(function() {
            Route::get('index',                     'VehicleController@attendantIndex');
            Route::get('edit/{id?}',                'VehicleController@editAttendant');
            Route::post('store/{id?}',              'VehicleController@storeAttendant');
        });
    });

    // prices
    Route::prefix('prices')->group(function() {
        Route::get('/',                             'PriceController@show');
        Route::get('index',                         'PriceController@index');
        Route::get('edit/{id?}',                    'PriceController@edit');
        Route::post('store/{id?}',                  'PriceController@store');

        // specials
        Route::prefix('special')->group(function() {
            Route::get('index',                     'PromotionController@index');
            Route::get('create',                    'PromotionController@create');
            Route::get('edit/{id?}/{name?}',        'PromotionController@edit');
            Route::post('store/{id?}',              'PromotionController@store');
            Route::get('load/{name}',               'PromotionController@loadExisting');
        });

        // promotions
        Route::prefix('promotion')->group(function() {
            Route::get('index',                     'PromotionController@promoIndex');
            Route::get('edit/{id?}',                'PromotionController@promoEdit');
            Route::post('store/{id?}',              'PromotionController@promoStore');
        });
    });

    // schools
    Route::prefix('schools')->group(function() {
        Route::get('/',                             'SchoolController@show');
        Route::get('find/{string}',                 'SchoolController@search');
        Route::get('index/{q?}',                    'SchoolController@index');
        Route::get('create',                        'SchoolController@create');
        Route::get('edit/{id}',                     'SchoolController@edit');
        Route::post('store/',                       'SchoolController@store');
        Route::post('update/{id}',                  'SchoolController@update');
        Route::get('schools/{city}',                'SchoolController@selectSchools');
    });

    // users
    Route::prefix('users')->group(function() {
        Route::get('/',                             'UsersController@show');
        Route::get('find/{string}',                 'UsersController@search');
        Route::get('bulkmail',                      'UsersController@bulkMail');
        Route::post('bulkmail/send',                'UsersController@bulkSend');

        // customers
        Route::prefix('customers')->group(function() {
            Route::get('index/{q?}',                'UsersController@index');
            Route::get('create',                    'UsersController@create');
            Route::get('edit/{id}',                 'UsersController@edit');
            Route::post('store/',                   'UsersController@store');
            Route::post('update/{id}',              'UsersController@update');
            Route::get('activate/{id}',             'UsersController@restore');
        });

        // guardians
        Route::prefix('guardians')->group(function() {
            Route::get('edit/{id}',                 'GuardianController@edit');
            Route::post('store/',                   'GuardianController@store');
        });

        // children
        Route::prefix('children')->group(function() {
            Route::get('photos',                    'ChildrenController@photos');
            Route::get('edit/{id}',                 'ChildrenController@show');
            Route::get('form/{id}/{child}',         'ChildrenController@form');
            Route::post('store/',                   'ChildrenController@store');
            Route::post('update/{id}',              'ChildrenController@update');
            Route::get('select/{parent}',           'ChildrenController@selectChildren');
        });

        // xmurals
        Route::prefix('xmurals')->group(function() {
            Route::get('edit/{id}',                 'XmuralController@show');
            Route::get('form/{id}/{xmural}',        'XmuralController@form');
            Route::post('store/',                   'XmuralController@store');
            Route::post('update/{id}',              'XmuralController@update');
        });

        // admins
        Route::prefix('admins')->group(function() {
            Route::get('index/{q?}',                'AdminController@index');
            Route::get('create',                    'AdminController@create');
            Route::get('edit/{id}',                 'AdminController@edit');
            Route::post('store/',                   'AdminController@store');
            Route::post('update/{id}',              'AdminController@update');
        });
    });
});

Route::middleware(['auth', 'can:enterOffice'])->group(function () {
    Route::get('loginas/{id}', function ($id) {
        $user = \App\Models\User::find($id);
        Auth::login($user);

        return redirect('/');
    });
});

// testing
Route::prefix('testing')->group(function() {
    Route::get('/',                                 'TestingController@show');
    Route::get('stats',                             'TestingController@vehicleStats');
    Route::get('duration',                          'TestingController@dropoffDuration');
    Route::get('daily',                             'TestingController@dailyTimes');
    Route::get('customers',                         'TestingController@customerRanking');
    Route::get('passengers',                        'TestingController@passengers');
    Route::get('passenger/{name}',                  'TestingController@passengerLifts');
    Route::get('school/{id}',                       'TestingController@schoolLifts');
    Route::get('build/statements',                  'TestingController@buildStatements');
    Route::get('seeding',                           'SeedingController@seedTables');
    Route::get('seed/statement',                    'SeedingController@seedStatements');

});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
