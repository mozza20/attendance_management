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
        $startTime = $this->start_time ?? Carbon::today()->setTime(12, 0, 0);  // 休憩開始
        $endTime = $this->end_time ?? Carbon::today()->setTime(13, 0, 0);      // 休憩終了

        $breakTotal = Carbon::instance($endTime)->diffInSeconds(Carbon::instance($startTime));

        return [
            'attendance_id' => $this->attendance_id ?? 1, 
            'start_time' => Carbon::instance($startTime)->format('H:i:s'),
            'end_time'   => Carbon::instance($endTime)->format('H:i:s'),
            'break_total' => $breakTotal,
        ];
    }
}
