<?php

namespace Database\Factories;

use App\Models\BreakTime;
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
        return [
            'attendance_id'=>$this->faker->numberBetween(1,180),
            'start_time'=>$this->faker->time(),
            'end_time'=>$this->faker->time(),
        ];
    }
}
