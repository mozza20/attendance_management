<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Database\Seeders\StatusesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusesTableSeeder::class);
    }

    //日時取得機能
    public function test_attendance_displays_correct_datetime(){
        $user = User::factory()->create();
        $this->actingAs($user);

        //テスト用に現在時刻を固定
        $fixedNow = CarbonImmutable::create(2025, 8, 30, 10, 15);
        CarbonImmutable::setTestNow($fixedNow);

        //勤怠画面にアクセス
        $response = $this->get('/attendance'); 

        $response->assertStatus(200);

        //ビューのdateとtimeを確認
        $response->assertViewHas('date', $fixedNow->isoFormat('Y年M月DD日(ddd)'));
        $response->assertViewHas('time', $fixedNow->format('H:i'));
    }

    //ステータス確認機能
    /**
     * @dataProvider statusProvider
     */
    public function test_attendance_status_is_correct($statusId){
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $statusId,
            'date' => now()->toDateString(),
        ]);

        $response = $this->get('/attendance');
        $response->assertStatus(200); 
        $response->assertViewHas('status_id', $statusId);
        
        $statusTexts = [
            1 => '勤務外',
            2 => '出勤中',
            3 => '休憩中',
            4 => '退勤済',
        ];
        $response->assertSee($statusTexts[$statusId]);
    }

    public static function statusProvider(): array{
        return [
            [1],
            [2],
            [3],
            [4],
        ];
    }
}
