<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BreakTimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        $attendance = Attendance::factory()->create();

        // 昼休憩（必ず）
        BreakTime::factory()->lunchBreak()->create([
            'attendance_id' => $attendance->id,
        ]);

        // 午後休憩（50%の確率）
        if (rand(0, 1)) {
            BreakTime::factory()->afternoonBreak()->create([
                'attendance_id' => $attendance->id,
            ]);
        }

        $start = Carbon::parse($attendance->start_time);
        $finish = Carbon::parse($attendance->finish_time);
        $breakSeconds = $attendance->breakTimes->sum('break_total');

        $attendance->work_total = max($finish->diffInSeconds($start) - $breakSeconds, 0);
        $attendance->save();
    }
}
