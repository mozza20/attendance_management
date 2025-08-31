<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Database\Seeders\StatusesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;
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

    //出勤機能
    public function test_work_attendance_button(){
        $user = User::factory()->create();
        $this->actingAs($user);

        //出勤ボタンが表示されているか
        $response = $this->get('/attendance');
        $response->assertStatus(200); 
        $response->assertSee('出勤');

        //出勤ボタンを押す
        $fixedNow = CarbonImmutable::create(2025, 8, 30, 10, 15);
        Carbon::setTestNow($fixedNow);

        $response = $this->post('/attendance');
        $response->assertStatus(302); 
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();
        $this->assertNotNull($attendance);
        $this->assertEquals(2, $attendance->status_id);

        //勤務中表示になっているか
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
        
        // 出勤ボタンが存在しないことを確認
        $response->assertDontSee('<button name="action" value="work">',false);

        //勤怠一覧の出勤時刻と一致するか
        $response = $this->get('/attendance/list');
        $response->assertStatus(200); 

        $this->assertEquals(
            $fixedNow->format('H:i'),
            Carbon::parse($attendance->start_time)->format('H:i')
        );
        $response->assertSee($fixedNow->format('H:i'));
    }
}
