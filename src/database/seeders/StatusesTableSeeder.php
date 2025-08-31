<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses=[
            1=>"勤務外",
            2=>"出勤中",
            3=>"休憩中",
            4=>"退勤済",
        ];

        foreach ($statuses as $id => $status) {
            DB::table('statuses')->insert([
                'id' => $id,
                'status' => $status,
            ]);
        }
    }
}
