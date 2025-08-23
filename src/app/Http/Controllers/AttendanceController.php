<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\RevData;
use App\Models\RevBreak;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    //一般・管理者共通

    //勤怠の詳細・修正画面表示
    public function show($attendance_id){
        if(Auth::user()->isAdmin){
            $attendance=Attendance::with('user')->findOrFail($attendance_id);
            $user=$attendance->user;
        }else{
            $user=Auth::user();
            $user_id=$user->id;
            $attendance=Attendance::where('user_id',$user_id)
            ->findOrFail($attendance_id);
        }

        $breakTimes=BreakTime::where('attendance_id',$attendance_id)->get();
        $breakCount=$attendance->breakTimes()->count();

        $revData=optional(RevData::where('attendance_id', $attendance_id)->first())->toArray();
        $revBreaks=optional(RevBreak::where('attendance_id', $attendance_id)->get())->toArray();

        $revBreaks=collect($revBreaks)->filter(function($revBreak){
            return !empty($revBreak['rev_start_time']) && !empty($revBreak['rev_end_time']);
        });        

        return view('common.attendanceDetail',compact('user','attendance','breakTimes','breakCount','revData','revBreaks'));
    }

    //勤怠の修正
    public function edit(AttendanceRequest $request, $attendance_id){
        if(Auth::user()->isAdmin){
            $attendance=Attendance::with('user')->findOrFail($attendance_id);
            $user=$attendance->user;
        }else{
            $user=Auth::user();
            $user_id=$user->id;
            $attendance=Attendance::where('user_id',$user_id)
            ->findOrFail($attendance_id);
        }

        $breakTimes=BreakTime::where('attendance_id',$attendance_id)->get();

        $rev_start = $request->input('rev_start_time', $attendance->start_time);
        $rev_finish = $request->input('rev_finish_time', $attendance->finish_time);

        $revBreaks = $request->input('breaks'); //配列で取得

        //勤務時間の計算
        $start=Carbon::parse($rev_start);
        $finish=Carbon::parse($rev_finish);
        $workSeconds=$finish->diffInSeconds($start);

        //休憩時間の計算
        $breakSeconds=0;
        foreach($revBreaks as $revBreak){
            if (!empty($revBreak['rev_start_time']) && !empty($revBreak['rev_end_time'])) {
                $breakStart = Carbon::parse($revBreak['rev_start_time']);
                $breakEnd = Carbon::parse($revBreak['rev_end_time']);
                $breakSeconds += $breakEnd->diffInSeconds($breakStart);
            }
        }

        $rev_work_total=max($workSeconds-$breakSeconds, 0);

        //RevData保存
        $revData=RevData::updateOrCreate(
            ['attendance_id'=>$attendance->id],
            [
                'rev_start_time' => $rev_start,
                'rev_finish_time' => $rev_finish,
                'rev_work_total' => $rev_work_total,
                'remarks' => $request->input('remarks',$attendance->remarks),
            ]);

        // RevBreaks 保存
        foreach ($revBreaks as $revBreak) {
            if (!empty($revBreak['id']) && !empty($revBreak['rev_start_time']) && !empty($revBreak['rev_end_time'])) {
                $breakTimeId = $revBreak['id'];

                RevBreak::updateOrCreate(
                    ['id'=>$revBreak['id']],
                    [
                        'attendance_id' => $attendance->id,
                        'break_time_id' => $breakTimeId,
                        'rev_start_time' => $revBreak['rev_start_time'],
                        'rev_end_time' => $revBreak['rev_end_time'],
                        'rev_break_total'=>$breakSeconds,
                    ]);
            }
        }
        $revBreaks=RevBreak::where('attendance_id', $attendance->id)->get();

        //勤怠ステータス更新
        $attendance->accepted = 1;
        $attendance->save();

        $breakCount=$attendance->revBreaks()->count();

        return view('common.attendanceDetail',compact('user','attendance','breakTimes','revData','revBreaks','breakCount'));
    }

    //申請一覧の表示
    public function showRequest(Request $request){
        $user_id=Auth::id();
        
        if(!Auth::user()->isAdmin){
            //一般ユーザのとき、自分の勤怠データを取得
            $attendances=Attendance::where('user_id',$user_id)->get();
        }else{
            //管理者のとき、全員の勤怠データを取得
            $attendances=Attendance::with('user')->get();
        }

        $userIds=$attendances->pluck('user_id');

        $tab=$request->input('tab','pending');
        
        if($tab==='accepted'){
            //承認済みの時
            $submittedData=Attendance::whereIn('user_id',$userIds)->where('accepted','2')->get();
            $requestSt="承認済み";
        }else{
            //承認待ちの時
            $submittedData=$attendances->where('accepted','1');
            $requestSt="承認待ち";
        }

        $attendanceIds=$submittedData->pluck('id');
        
        $revData=RevData::whereIn('attendance_id', $attendanceIds)->get();
        $revBreaks=RevBreak::whereIn('attendance_id', $attendanceIds)->get();

        return view('common.request',compact('userIds','user_id','submittedData','revData','revBreaks','requestSt'));
    }
}
