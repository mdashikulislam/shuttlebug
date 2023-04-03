<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(ChildrenTableSeeder::class);
        $this->call(GuardiansTableSeeder::class);
        $this->call(SchoolsTableSeeder::class);
        $this->call(XmuralsTableSeeder::class);
        $this->call(UserXmuralsTableSeeder::class);
        $this->call(SuburbsTableSeeder::class);
        $this->call(BookingsTableSeeder::class);
        $this->call(VehiclesTableSeeder::class);
        $this->call(DriversTableSeeder::class);
        $this->call(PricesTableSeeder::class);
        $this->call(PromotionsTableSeeder::class);
        $this->call(PublicholidaysTableSeeder::class);
        $this->call(SchooltermsTableSeeder::class);
        $this->call(SchoolholidaysTableSeeder::class);
        $this->call(EventBookingsTableSeeder::class);
        $this->call(DebtorsJournalTableSeeder::class);
        $this->call(DebtorsStatementTableSeeder::class);
        $this->call(TripSettingsTableSeeder::class);
        $this->call(PlanningReportsTableSeeder::class);
    }
}
