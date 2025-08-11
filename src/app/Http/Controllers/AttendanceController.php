<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\RevData;
use App\Models\RevBreak;

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

    //申請の表示
    public function showRequest(Request $request){
        $user=Auth::user();

        $tab=$request->input('tab','pending');
        
        if($tab==='accepted'){
            //承認済みの時
            $submittedData=Attendance::where('user_id',$user->id)->where('accepted','2')->get();
            $requestSt="承認済み";
        }else{
            //承認待ちの時
            $submittedData=Attendance::where('user_id',$user->id)->where('accepted','1')->get();
            $requestSt="承認待ち";
        }

        $attendanceIds=$submittedData->pluck('id');
        
        $revData=RevData::whereIn('attendance_id', $attendanceIds)->get();
        $revBreaks=RevBreak::whereIn('attendance_id', $attendanceIds)->get();

        return view('common.request',compact('user','submittedData','revData','revBreaks','requestSt'));
    }
}
