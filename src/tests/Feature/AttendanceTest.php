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

class AttendanceTest extends TestCase
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

    //出勤機能------------------------------------------------------
    public function test_work_attendance_button(){
        $user = User::factory()->create();
        $this->actingAs($user);

        //出勤ボタンが表示されているか
        $response = $this->get('/attendance');
        $response->assertStatus(200); 
        $response->assertSee('出勤');

        //出勤ボタンを押す
        $fixedNow = CarbonImmutable::create(2025, 8, 30, 8, 0);
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

        Carbon::setTestNow(); // リセット
    }

    //退勤機能------------------------------------------------------
    public function test_work_leave_button(){
        $user = User::factory()->create();
        $this->actingAs($user);

        $fixedNow = CarbonImmutable::create(2025, 8, 30, 8, 0);
        Carbon::setTestNow($fixedNow);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $fixedNow->toDateString(),
            'status_id' => 2,
            'start_time' => $fixedNow->format('H:i:s'),
        ]);

        //退勤ボタンが表示されているか
        $response = $this->get('/attendance');
        $response->assertStatus(200); 
        $response->assertSee('退勤');

        //退勤ボタンを押す
        $fixedNow = CarbonImmutable::create(2025, 8, 30, 17, 0);
        Carbon::setTestNow($fixedNow);

         $response = $this->post('/attendance',[
            'action' => 'leave',
            'finish_time' => $fixedNow->format('H:i:s'),
        ])->assertStatus(302); 
        $attendance = $attendance->fresh();
        $this->assertEquals(4, $attendance->status_id);

        //退勤済表示になっているか
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');

        //勤怠一覧の退勤時刻と一致するか
        $response = $this->get('/attendance/list');
        $response->assertStatus(200); 

        $this->assertEquals(
            $fixedNow->format('H:i'),
            Carbon::parse($attendance->finish_time)->format('H:i')
        );
        $response->assertSee($fixedNow->format('H:i'));

        Carbon::setTestNow(); // リセット
    }


    //休憩機能------------------------------------------------------
    public function test_work_break_button(){
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $fixedNow = CarbonImmutable::create(2025, 8, 30, 8, 0);
        Carbon::setTestNow($fixedNow);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $fixedNow->toDateString(),
            'status_id' => 2,
            'start_time' => $fixedNow->format('H:i:s'),
        ]);

        //休憩入ボタンが表示されているか
        $response = $this->get('/attendance');
        $response->assertStatus(200); 
        $response->assertSee('休憩入');

        //休憩入ボタンを押す
        $breakStart = CarbonImmutable::create(2025, 8, 30, 12, 0);
        Carbon::setTestNow($breakStart);

        $response = $this->post('/attendance',[
            'action' => 'break',
            'start_time' => $breakStart->format('H:i:s'),
        ])->assertStatus(302); 
        $attendance = $attendance->fresh();
        $breakTime = BreakTime::where('attendance_id', $attendance->id)->latest()->first();

        $this->assertEquals(3, $attendance->status_id);
        $this->assertEquals($breakStart->format('H:i'), Carbon::parse($breakTime->start_time)->format('H:i'));


        //休憩中表示になっているか、休憩戻るボタンが表示されているか
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');

        //休憩戻ボタンを押す
        $backToWork = CarbonImmutable::create(2025, 8, 30, 13, 0);
        Carbon::setTestNow($backToWork);

        $response = $this->post('/attendance',['action' => 'work'])->assertStatus(302);
        $attendance = $attendance->fresh(); 
        $breakTime = BreakTime::where('attendance_id', $attendance->id)->latest()->first();

        $this->assertEquals(2, $attendance->status_id);
        $this->assertEquals($backToWork->format('H:i'), Carbon::parse($breakTime->end_time)->format('H:i'));

        //休憩繰り返し------------------------------------
        //休憩入ボタンが表示されているか
        $response = $this->get('/attendance');
        $response->assertStatus(200); 
        $response->assertSee('休憩入');

        //休憩入ボタンを押す
        $breakStart2 = CarbonImmutable::create(2025, 8, 30, 15, 00);
        Carbon::setTestNow($breakStart2);

        $response = $this->post('/attendance',['action' => 'break'])->assertStatus(302); 
        $attendance = $attendance->fresh(); 
        $breakTime = BreakTime::where('attendance_id', $attendance->id)->latest()->first();

        $this->assertEquals(3, $attendance->status_id);
        $this->assertEquals($breakStart2->format('H:i'), Carbon::parse($breakTime->start_time)->format('H:i'));

        //休憩中表示になっているか、休憩戻るボタンが表示されているか
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');

        //休憩戻ボタンを押す
        $backToWork2 = CarbonImmutable::create(2025, 8, 30, 15, 10);
        Carbon::setTestNow($backToWork2);

        $response = $this->post('/attendance',['action' => 'work'])->assertStatus(302); 
        $attendance = $attendance->fresh(); 
        $breakTime = BreakTime::where('attendance_id', $attendance->id)->latest()->first();

        $this->assertEquals(2, $attendance->status_id);
        $this->assertEquals($backToWork2->format('H:i'), Carbon::parse($breakTime->end_time)->format('H:i'));

        //勤怠一覧の時刻と一致するか
        $response = $this->get('/attendance/list');
        $response->assertStatus(200); 

        $response->assertSee($breakStart->format('H:i'));
        $response->assertSee($backToWork->format('H:i'));
        $response->assertSee($breakStart2->format('H:i'));
        $response->assertSee($backToWork2->format('H:i'));

        Carbon::setTestNow(); // リセット
    }
}
