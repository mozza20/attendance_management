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
    public function definition(){
        return [
            'attendance_id' => null,
            'start_time'    => '12:00:00',
            'end_time'      => '13:00:00',
            'break_total'   => 3600,
        ];
    }

    // 昼休憩
    public function lunchBreak(){
        return $this->state(fn() => [
            'start_time'  => '12:00:00',
            'end_time'    => '13:00:00',
            'break_total' => 3600,
        ]);
    }

    // 午後休憩
    public function afternoonBreak(){
        return $this->state(fn() => [
            'start_time'  => '15:00:00',
            'end_time'    => '15:10:00',
            'break_total' => 600,
        ]);
    }
}
