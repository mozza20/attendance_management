<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        //勤務時間を取得(ダミー180件)
        $attendanceId=$this->faker->numberBetween(1,180);
        $attendance=\App\Models\Attendance::find($attendanceId);

        $startTime = Carbon::today()->setTime(8, 0, 0);
        $endTime = Carbon::today()->setTime(20, 0, 0);

        //退勤時間入っていなくても20時までの時間を設定
        if ($attendance) {
            $startTime = Carbon::parse($attendance->start_time);
            $endTime = Carbon::parse($attendance->finish_time ?? '20:00:00');
        }

        $start = $this->faker->dateTimeBetween($startTime, $endTime);
        $finish = $this->faker->dateTimeBetween($start, $endTime);

        $breakTotal=Carbon::instance($finish)->diffInSeconds(Carbon::instance($start));

        return [
            'attendance_id'=>$attendanceId,
            'start_time'=>Carbon::instance($start)->format('H:i:s'),
            'end_time'=>Carbon::instance($finish)->format('H:i:s'),
            'break_total'=>$breakTotal,
        ];
    }
}
