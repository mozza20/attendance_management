<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Status;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\RevData;
use App\Models\RevBreak;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class AdminController extends Controller
{
    //勤怠一覧表示(日次)
    public function index(Request $request){
        $ymd=$request->input('ymd');

        if ($ymd) {
            $currentDate = CarbonImmutable::parse($ymd);
        } else {
            $currentDate = CarbonImmutable::today();
        }

        if($request->input('shift')==='back'){
            $currentDate = $currentDate->subDay();
        }elseif($request->input('shift')==='next'){
            $currentDate = $currentDate->addDay();
        }

        $dayBefore = $currentDate->subDay();
        $nextDay = $currentDate->addDay();

        $attendanceData = Attendance::where('date',$currentDate)
        ->get();

        $attendance_ids = $attendanceData->pluck('id');
        $breakTimes = BreakTime::whereIn('attendance_id',$attendance_ids)->get();

        return view('admin.adminAttendanceList',[
            'attendances' => $attendanceData,
            'breakTimes' => $breakTimes,
            'currentDate' => $currentDate,
        ]);
    }

    //スタッフ一覧表示
    public function staffIndex(){
        $users = Auth::user()->get();
        return view('admin.staff',compact('users'));
    }

    //勤怠一覧表示(スタッフ別)
     public function showStaff(Request $request, $user_id){
        $user = User::find($user_id);

        $ym=$request->input('ym');
        if($ym){
            $currentYM = CarbonImmutable::createFromFormat('Y-m',$ym)->startOfMonth();
        }else{
            $currentYM = CarbonImmutable::now()->startOfMonth();
        }

        if($request->input('shift') === 'back'){
            $currentYM = $currentYM->subMonth();
        }elseif($request->input('shift') === 'next'){
            $currentYM = $currentYM->addMonth();
        }

        $startOfMonth = $currentYM->startOfMonth();
        $endOfMonth = $currentYM->endOfMonth();
        $thisMonth = $currentYM->isoFormat('Y/MM');

        $attendanceData = Attendance::where('user_id',$user_id)
        ->whereBetween('date',[$startOfMonth,$endOfMonth])
        ->get();

        $attendance_ids = $attendanceData->pluck('id');
        $breakTimes = BreakTime::whereIn('attendance_id',$attendance_ids)->get();

        return view('user.attendanceList',[
            'user' => $user,
            'user_id' => $user_id,
            'attendances' => $attendanceData,
            'breakTimes' => $breakTimes,
            'thisMonth' => $thisMonth,
            'currentYM' => $currentYM->format('Y-m'),
        ]);
    }

    //承認用_勤怠詳細表示
    public function showDetail($attendance_id){
        $attendance = Attendance::with('user','breakTimes')->findOrFail($attendance_id);
        $user=$attendance->user;
        $breakTimes = BreakTime::where('attendance_id', $attendance_id)->get();

        $revData=RevData::where('attendance_id',$attendance_id)->first();
        $revBreaks = RevBreak::where('attendance_id',$attendance_id)->get();

        return view('admin.adminAttendanceDetail',compact('attendance','user','breakTimes','revData','revBreaks','attendance_id'));
    }

    //承認処理
    public function update(Request $request, $attendance_id){
        $revData=RevData::where('attendance_id',$attendance_id)->first();
        $attendance = Attendance::with('user','breakTimes')->findOrFail($attendance_id);

        //Attendance上書き
        $attendance->fill([
            'start_time'  => $revData->rev_start_time,
            'finish_time' => $revData->rev_finish_time,
            'work_total'  => $revData->rev_work_total,
            'remarks'     => $revData->remarks,
            'accepted'    => 2,
        ]);
        $attendance->save();

        $breakTimes = BreakTime::where('attendance_id', $attendance_id)->get();
        $revBreaks = RevBreak::where('attendance_id',$attendance_id)->get();

        //BreakTime上書き
        foreach ($breakTimes as $breakTime) {
            $rev = $revBreaks->firstWhere('break_time_id', $breakTime->id);
            if ($rev) {
                $breakTime->start_time  = $rev->rev_start_time;
                $breakTime->end_time    = $rev->rev_end_time;
                $breakTime->break_total = $rev->rev_break_total;

                $breakTime->save();
            }
        }
        return redirect()->route('requestDetail.show',$attendance_id);
    }

}
