<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        //管理者以外のユーザーIDを配列にする
        $users = User::where('isAdmin', 0)->pluck('id'); 

        $startDate = Carbon::today()->subMonth(3);
        $endDate = Carbon::today()->subDay();

        foreach($users as $userId){
            $currentDate = $startDate->copy();
            while($currentDate->lte($endDate)){
                //30%の確率で欠勤
                if(rand(1, 100)>30){
                    Attendance::factory()->create([
                        'user_id'=> $userId,
                        'date' =>$currentDate->format('Y-m-d'),
                    ]);
                }
                $currentDate->addDay();
            }
        }
    }
}
