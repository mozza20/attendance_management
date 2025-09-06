<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\BreakTime;
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
                    $attendance = Attendance::factory()->create([
                        'user_id'=> $userId,
                        'date' =>$currentDate->format('Y-m-d'),
                        'start_time' => '08:00:00',
                        'finish_time'=> '17:00:00',
                        'status_id' => 4,
                    ]);
                    
                    if($attendance){
                        BreakTime::factory()->create([
                            'attendance_id' => $attendance->id,
                            'start_time' => '12:00:00',
                            'end_time' => '13:00:00',
                            'break_total' => 3600,
                        ]);                    

                        // 午後10分休憩（ランダムで作る場合）
                        if(rand(0,1)){
                            BreakTime::factory()->create([
                                'attendance_id' => $attendance->id,
                                'start_time' => '15:00:00',
                                'end_time' => '15:10:00',
                                'break_total' => 600,
                            ]);
                        }
                    }

                    // work_totalを計算
                    $start = Carbon::parse($attendance->start_time);
                    $finish = Carbon::parse($attendance->finish_time);
                    $breakSeconds = $attendance->breakTimes->sum('break_total');

                    $attendance->work_total = max($finish->diffInSeconds($start) - $breakSeconds, 0);
                    $attendance->save();

                }else{
                    // 欠勤のレコード
                    Attendance::create([
                        'user_id' => $userId,
                        'date' => $currentDate->format('Y-m-d'),
                        'start_time' => null,
                        'finish_time' => null,
                        'status_id' => 1,
                    ]);
                }
                $currentDate->addDay();
            }
        }
    }
}
