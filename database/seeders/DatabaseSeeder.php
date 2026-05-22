<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Domain\Drivers\Models\Entities\Driver;
use Src\Domain\Orders\Models\Entities\Order;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        Driver::factory()->count(20)->create();           // available
        Driver::factory()->count(4)->offline()->create();  // off shift

        Order::factory()->count(30)->create();             // pending, awaiting assignment
    }
}
