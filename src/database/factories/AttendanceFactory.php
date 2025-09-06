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
        $baseDate = Carbon::instance($this->faker->dateTimeThisMonth());

        $startTime = $this->start_time ?? null;
        $finishTime = $this->finish_time ?? null;

        $workSeconds = ($startTime && $finishTime) ? $finishTime->diffInSeconds($startTime) : 0;

        return [
            'user_id' => $this->user_id ?? 1,
            'date' => $this->date ?? $baseDate,
            'start_time'=> $startTime ? $startTime->format('H:i:s') : null,
            'finish_time'=> $finishTime ? $finishTime->format('H:i:s') : null,
            'work_total'=> $workSeconds,
            'status_id' => $this->status_id ?? 4,
        ];
    }
}
