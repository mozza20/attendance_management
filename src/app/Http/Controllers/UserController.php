<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

use DateTime;

class UserController extends Controller
{
    public function attendance(){
        $now=new DateTime();
        $date=$now->format('Y年n月j日');
        $time=$now->format('H:i');
        $status_id=Auth::user()->status_id;
        return view('user.attendance',compact('date','time','status_id'));
    }

    public function input(Request $request){
        $user=Auth::user();
        $user_id=$user->id;
        $attendances=Attendance::where('user_id',$user_id)
            ->where('date',now()->toDateString())
            ->first();
        $status=$user->status_id;

        switch($status){
            case 0: //勤務外→出勤
                $attendance=Attendance::create([
                    'user_id'=>'$user->id',
                    'date'=>now()->toDateString(),
                    'start_time'=>now()->toTimeString(),
                    'status_id'=>1,
                ]);
                $user->update(['status_id'=>1]);
                break;

            case 1: //出勤→休憩開始 or 退勤
                if($request->input('action')==='break'){
                    //休憩開始
                    $attendance=breakTimes()->create([
                        'start_time'=>now()->toTimeString(),
                    ]);
                    $user->update(['status_id'=>2]);
                }elseif($request->input('action')==='leave'){
                    //退勤
                    $start=Carbon::parse($attendance->start_time);
                    $end=now();
                    $workMitutes=$start->diffInMinutes($end);
                    $totalBreak=$attendance->breakTimes()->sum('break_total');

                    $attendance->update([
                        'finish_time'=>$end->toTimeString(),
                        'work_total'=>$workMinutes-$totalBreak,
                    ]);
                    $user->update(['status_id'=>3]);
                }
                break;

            case 2: //休憩中→休憩終了
                $break=$attedance->breakTimes()->whereNull('end_time')->latest()->first();
                $break->update([
                    'end_time'=>now()->toTimeString(),
                    'break_total'=>now()->diffInMinutes(Carbon::parse($break->start_time)),
                ]);
                $user->update(['status_id'=>1]);
                break;

            case 3: //退勤済み
                return back();
        }
        return back();
    }

    public function index(){
        return view('user.attendanceList');
    }

    public function show(){
        $user=Auth::user();
        return view('user.attendanceDetail',compact('user'));
    }

}
