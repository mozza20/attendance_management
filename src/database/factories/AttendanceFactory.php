<?php

namespace Database\Factories;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $model = Attendance::class;

    public function definition(){
        $baseDate = $this->faker->dateTimeThisMonth(); 

        //勤務時間を8:00~20:00の間に設定
        $startTime = $baseDate->copy()->setTime(8, 0, 0);
        $endTime = $baseDate->copy()->setTime(20, 0, 0);

        // 出勤・退勤時間
        $start = $this->faker->dateTimeBetween($startTime, $endTime);
        $finish = $this->faker->dateTimeBetween($start, $endTime);

        //仮の休憩時間 (後でBreakTimeFactoryで上書き)
        $dummyBreakSeconds=rand(0,60*60);

        //勤務時間
        $workSeconds = Carbon::instance($finish)->diffInSeconds(Carbon::instance($start)) - $dummyBreakSeconds;
        $workSeconds = max($workSeconds, 0); // マイナスを防ぐ

        return [
            'user_id' => 1,
            'date' => $baseDate,
            'start_time'=> Carbon::instance($start)->format('H:i:s'),
            'finish_time'=> Carbon::instance($finish)->format('H:i:s'),
            'work_total'=>$workSeconds,
            'status_id' => 4,
        ];
    }
}
