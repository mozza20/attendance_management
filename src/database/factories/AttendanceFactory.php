<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // 任意の日付・今日など
        $baseDate = Carbon::today();

        // 開始時間（例: 9時）
        $startTime = $baseDate->copy()->setTime(8, 0, 0);
        // 終了時間（例: 18時）
        $endTime = $baseDate->copy()->setTime(20, 0, 0);

        // start_time を9:00〜20:00の間で生成
        $start = $this->faker->dateTimeBetween($startTime, $endTime);

        // finish_time は start_time 以降、20時までの間で生成
        $finish = $this->faker->dateTimeBetween($start, $endTime);

        return [
            'user_id' => $this->faker->numberBetween(1,3),
            'date' => $this->faker->dateTimeBetween('-3 month','now'),
            'start_time'=> $start->format('H:i:s'),
            'finish_time'=> $finish->format('H:i:s'),
            'status_id' => $this->faker->numberBetween(1, 4),
        ];
    }
}
