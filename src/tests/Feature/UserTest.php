<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Database\Seeders\StatusesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    
    use RefreshDatabase;

    protected function setUp(): void{
        parent::setUp();
        $this->seed(StatusesTableSeeder::class);
    }

    protected function createUserWithAttendance(array $attendanceOverrides = []){
        // ユーザーを作成
        $user = User::factory()->create();

        // 勤怠データを作成
        $attendance = Attendance::factory()->create(array_merge([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'finish_time' => '18:00:00',
            'status_id' => 4,
        ], $attendanceOverrides));

        return [$user, $attendance];
    }

    //休憩データを作成
    protected function createBreakTimes(Attendance $attendance){
    $patterns = [
        ['start_time' => '12:00:00', 'end_time' => '13:00:00'], 
        ['start_time' => '15:00:00', 'end_time' => '15:15:00'], 
    ];

    $breaks = [];
    foreach ($patterns as $pattern) {
        $breaks[] = BreakTime::factory()->create(array_merge([
            'attendance_id' => $attendance->id,
        ], $pattern));
    }

    return $breaks;
}


    //勤怠一覧情報取得機能
    public function test_show_own_attendances(){
        [$user, $attendance] = $this->createUserWithAttendance();
        $this->actingAs($user);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee(formatJapaneseDate($attendance->date));
        $response->assertSee(substr($attendance->start_time, 0, 5));
        $response->assertSee(substr($attendance->finish_time, 0, 5));
    }

    //現在の月を表示
    public function test_monthly_display(){
        [$user] = $this->createUserWithAttendance([
            'date' => '2025-09-01'
        ]);
        $this->actingAs($user);

        $fixedNow = CarbonImmutable::create(2025, 9, 1, 10, 15);
        CarbonImmutable::setTestNow($fixedNow);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee($fixedNow->isoFormat('Y/MM'));
    }

    //前月データを表示
    public function test_previous_month_display(){
        [$user] = $this->createUserWithAttendance([
            'date' => '2025-08-15'
        ]);
        $this->actingAs($user);

        $this->get('/attendance/list?ym=2025-08')
            ->assertSee('2025/08');
    }

    //勤怠詳細表示
    public function test_display_attendance_detail(){
        [$user, $attendance] = $this->createUserWithAttendance();
        $this->actingAs($user);
        $attendanceId = $attendance->id;
        $breakTimes = $this->createBreakTimes($attendance);

        $response = $this->get('/attendance/detail/'.$attendanceId);
        $response->assertStatus(200);

        //ユーザー名が表示されているか
        $response->assertSee($user->name);

        //選択した日付が表示されているか
        $response->assertSee(formatJapaneseYear($attendance->date));
        $response->assertSee(formatJapaneseMD($attendance->date));

        //出勤・退勤時刻が打刻と一致しているか
        $response->assertSee(formatTime($attendance->start_time));
        $response->assertSee(formatTime($attendance->finish_time));

        //休憩時間が一致しているか
        $response->assertSee(formatTime($breakTimes[0]->start_time));
        $response->assertSee(formatTime($breakTimes[0]->end_time));
        $response->assertSee(formatTime($breakTimes[1]->start_time));
        $response->assertSee(formatTime($breakTimes[1]->end_time));
    }

    //勤怠エラーの確認
    public function test_display_error_messages(){
        [$user, $attendance] = $this->createUserWithAttendance();
        $this->actingAs($user);
        $attendanceId = $attendance->id;

        $response = $this->from('/attendance/detail/'.$attendanceId)        
            ->followingRedirects()
            ->post('/attendance/detail/confirm/'.$attendanceId, [
                'rev_start_time' => '18:00',
                'rev_finish_time' => '17:00',
                'breaks' => [
                    1=> ['rev_start_time' => '18:00', 'rev_end_time' => '18:15'],
                ],
            ]);

        //出勤>退勤の場合
        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');

        //休憩開始>退勤の場合
        $response->assertSee('休憩時間が不適切な値です');

        //休憩終了>退勤の場合
        $response->assertSee('休憩時間もしくは退勤時間が不適切な値です');
        
        //備考欄が未入力の場合
        $response->assertSee('備考欄を記入してください');
    }

}
