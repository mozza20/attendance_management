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

use Illuminate\Support\Facades\Log;


use DateTime;

class UserController extends Controller
{
    //勤怠登録画面表示
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

    //勤怠登録
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
                        'work_total'=>$workTime-$totalBreak,
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

    //勤怠一覧表示
    public function index(Request $request){
        $user=Auth::user();
        $user_id=$user->id;

        $ym=$request->input('ym');
        if($ym){
            $currentYM = CarbonImmutable::createFromFormat('Y-m',$ym)->startOfMonth();
        }else{
            $currentYM=CarbonImmutable::now()->startOfMonth();
        }

        if($request->input('shift')==='back'){
            $currentYM=$currentYM->subMonth();
        }elseif($request->input('shift')==='next'){
            $currentYM=$currentYM->addMonth();
        }

        $startOfMonth=$currentYM->startOfMonth();
        $endOfMonth=$currentYM->endOfMonth();
        $thisMonth=$currentYM->isoFormat('Y/MM');

        $attendanceData=Attendance::where('user_id',$user_id)
        ->whereBetween('date',[$startOfMonth,$endOfMonth])
        ->get();

        $attendance_ids=$attendanceData->pluck('id');
        $breakTimes=BreakTime::whereIn('attendance_id',$attendance_ids)->get();

        return view('user.attendanceList',[
            'attendances' => $attendanceData,
            'breakTimes' => $breakTimes,
            'thisMonth' => $thisMonth,
            'currentYM' => $currentYM->format('Y-m'),
        ]);
    }
}