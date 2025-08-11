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

    //勤怠の詳細・修正画面表示
    public function show($attendance_id){
        $user=Auth::user();
        $user_id=$user->id;
        $attendance=Attendance::where('user_id',$user_id)
        ->findOrFail($attendance_id);
        $breakTimes=BreakTime::where('attendance_id',$attendance_id)->get();

        $breakCount=$attendance->breakTimes()->count();

        $revData=optional(RevData::where('attendance_id', $attendance_id)->first())->toArray();
        $revBreaks=optional(RevBreak::where('attendance_id', $attendance_id)->get())->toArray();

        $revBreaks=collect($revBreaks)->filter(function($revBreak){
            return !empty($revBreak['rev_start_time']) && !empty($revBreak['rev_end_time']);
        });

        return view('user.attendanceDetail',compact('user','attendance','breakTimes','breakCount','revData','revBreaks'));
    }

    //勤怠の申請
    public function edit(Request $request, $attendance_id){
        $user=Auth::user();
        $attendance=Attendance::where('user_id',$user->id)
        ->findOrFail($attendance_id);
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

        return view('user.attendanceDetail',compact('user','attendance','breakTimes','revData','revBreaks','breakCount'));
    }

}
