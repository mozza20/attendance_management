<?php

namespace Database\Seeders;

use App\Models\BreakTime;
use Illuminate\Database\Seeder;

class BreakTimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BreakTime::factory()->count(100)->create();
    }
}
