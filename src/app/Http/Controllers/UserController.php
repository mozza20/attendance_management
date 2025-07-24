<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Status;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

use Illuminate\Support\Facades\Log;


use DateTime;

class UserController extends Controller
{
    public function attendance(){
        $now=CarbonImmutable::now();
        $date=$now->isoFormat('Y年M月DD日(ddd)');
        $time=$now->format('H:i');
        $user=\App\Models\User::find(Auth::id());//DBから最新情報を取得
        $attendance = \App\Models\Attendance::where('user_id', $user->id)
                    ->where('date', now()->toDateString())
                    ->first();

        $status_id = $attendance ? $attendance->status_id : 1;

        return view('user.attendance',compact('date','time','attendance','status_id'));
    }

    public function input(Request $request){
        $user=Auth::user();
        $user_id=$user->id;
        $attendance=Attendance::where('user_id',$user_id)
            ->where('date',now()->toDateString())
            ->first();
        $status_id = $attendance ? $attendance->status_id : 1;

        switch($status_id){
            case 1: //勤務外→出勤
                $attendance=Attendance::create([
                    'user_id'=>$user->id,
                    'date'=>now()->toDateString(),
                    'start_time'=>now()->toTimeString(),
                    'status_id'=>2,
                ]);
                break;

            case 2: //出勤→休憩開始 or 退勤
                if($request->input('action')==='break'){
                    //休憩開始
                    $attendance->breakTimes()->create([
                        'attendance_id'=>$attendance->id,
                        'start_time'=>now()->toTimeString(),
                    ]);
                    $attendance->update(['status_id'=>3]);

                }elseif($request->input('action')==='leave'){
                    //退勤
                    $start=Carbon::parse($attendance->start_time);
                    $end=now();
                    $workTime=$start->diffInSeconds($end);
                    $totalBreak=$attendance->breakTimes()->sum('break_total');

                    $attendance->update([
                        'finish_time'=>$end->toTimeString(),
                        'work_total'=>gmdate('G:i:s',$workTime-$totalBreak),
                        'status_id' => 4, // 退勤済みに更新
                    ]);
                }
                break;

            case 3: //休憩中→休憩終了
                $break=$attendance->breakTimes()->whereNull('end_time')->latest()->first();
                $endTime=now();
                $startTime=Carbon::parse($break->start_time);
                $diffInSeconds=$startTime->diffInSeconds($endTime);

                $break->update([
                    'end_time'=>now()->toTimeString(),
                    'break_total'=>$diffInSeconds,
                ]);
                $attendance->update(['status_id'=>2]);
                break;

            case 4: //退勤済み
                return back();
        }
        return back();
    }

    public function index(){
        $user=Auth::user();
        $user_id=$user->id;

        $now=CarbonImmutable::now();
        $startOfMonth=$now->startOfMonth();
        $endOfMonth=$now->endOfMonth();
        $thisMonth=$now->isoFormat('Y/MM');

        $attendances=Attendance::where('user_id',$user_id)
        ->whereBetween('date',[$startOfMonth,$endOfMonth])
        ->get();

        $attendance_ids=$attendances->pluck('id');
        $breakTimes=BreakTime::whereIn('attendance_id',$attendance_ids)->get();
    
        return view('user.attendanceList',compact('attendances','breakTimes','thisMonth'));
    }

    public function show($attendance_id){
        $user=Auth::user();
        $user_id=$user->id;
        $attendance=Attendance::where('user_id',$user_id)
        ->findOrFail($attendance_id);
        $breakTimes=BreakTime::where('attendance_id',$attendance_id)->get();
        
        return view('user.attendanceDetail',compact('user','attendance','breakTimes'));
    }

}
