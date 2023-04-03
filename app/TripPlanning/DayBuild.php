<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2018/04/01
 * Time: 11:36 AM
 */

/*
 * Day Build
 * Build the day trips using the prepared pickup trips.
 *
 * Allocates vehicles to each pickup.
 * Allocates passengers to each vehicle.
 * Builds the drop off route for each vehicle.
 *
 * The reporter accumulates information from each process
 * and is used on the trip planner for analysis and tweaking.
 */


namespace App\TripPlanning;


class DayBuild
{
    /**
     * @var DayReport
     * @var VehiclesAvailable
     * @var VehiclesLeg
     */
    protected $reporter;
    protected $availableVehicles;
    protected $legVehicles;
    protected $legPassengers;
    protected $legRoutes;

    /**
     * DayBuild constructor.
     *
     */
    public function __construct()
    {
        $this->reporter = new DayReport();
        $this->availableVehicles = new VehiclesAvailable();
        $this->legVehicles = new VehiclesLeg();
        $this->legPassengers = new VehiclePassengers();
        $this->legRoutes = new VehicleRoutes();
        $this->settings = session('planner.settings');
    }

    /**
     * Process the pickups
     *
     */
    public function run()
    {
        $pickups = session('planner.pickups');

        foreach ( $pickups as $leg => $pickup ) {

            $this->reporter->add('pickup', ['leg' => $leg, 'pickup' => $pickup]);

            /*
             * get available vehicles for this pickup
             */
            $available_vehicles = $this->availableVehicles->collect($pickup);
            $this->reporter->add('available', $available_vehicles);

            /*
             * Select the vehicles for this pickup
             */
            $leg_vehicles = $this->legVehicles->select($pickup, $available_vehicles, $pickups);
            $this->reporter->add('ranking', null);

            /*
             * Allocate passengers to leg vehicles
             */
            list($leg_vehicles, $leg_passengers) = $this->legPassengers->allocate($leg_vehicles, $pickup);
            $this->reporter->add('pass', null);
            $this->reporter->add('vzone', null);

            /*
             * Create drop off routes for leg vehicles
             */
            $this->legRoutes->route($leg_vehicles, $leg_passengers, $pickup, $pickups);
            $this->reporter->add('free', null);
            $this->reporter->move();

            // escape from planning at selected time (for testing purposes)
            if ( $leg == 99 ) {
                dump('fleet');dump(Fleet::list());
                dump('planner trips');dump(session("planner.trips"));
                dump('pickups');dump($pickups);
                dump('report');dump(session('planner.report'));
                return;
            }
        }

        return;
    }
}