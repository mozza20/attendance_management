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

    public function show($attendance_id){
        $user=Auth::user();
        $user_id=$user->id;
        $attendance=Attendance::where('user_id',$user_id)
        ->findOrFail($attendance_id);
        $breakTimes=BreakTime::where('attendance_id',$attendance_id)->get();

        $breakCount=$attendance->breakTimes()->count();

        return view('user.attendanceDetail',compact('user','attendance','breakTimes','breakCount'));
    }

    //承認待ち画面
    public function edit(Request $request, $attendance_id){
        $user=Auth::user();
        $attendance=Attendance::where('user_id',$user_id)
        ->findOrFail($attendance_id);

        $revData=$request->only([
            'id'=>$attendance->id,
            'rev_start_time' => $request->input('rev_start_time'),
            'rev_finish_time' => $request->input('rev_finish_time'),
            'work_total' => $request->input('work_total'),
            'remarks' => $request->input('remarks'),
            'attendance_id' => $attendance->id,
        ]);

        $revBreaks = $request->input('breaks'); //配列で取得

        return view('user.attendanceDetail',compact('user','revData','revBreaks'));
    }

    public function update(Request $request,$attendance_id){
        $user =Auth::user();
        $attendance=Attendance::where('user_id',$user->id)
        ->findOrFail($attendance_id);

        RevData::updateOrCreate(
            ['attendance_id'=>$attendance->id],
            [
                'rev_start_time' => $request->input('rev_start_time'),
                'rev_finish_time' => $request->input('rev_finish_time'),
                'rev_work_total' => $request->input('work_total'),
                'remarks' => $request->input('remarks'),
            ]
        );

         RevBreak::where('attendance_id', $attendance->id)->delete();

        $breaks = $request->input('breaks', []);
        foreach ($breaks as $breakInput) {
            //休憩の追加
            if (!empty($breakInput['rev_start_time']) && !empty($breakInput['rev_end_time'])) {
                $start = Carbon::parse($breakInput['rev_start_time']);
                $end = Carbon::parse($breakInput['rev_end_time']);
                RevBreak::create([
                    'attendance_id' => $attendance->id,
                    'break_time_id' => $breakInput['id'] ?? null, 
                    'rev_start_time' => $start,
                    'rev_end_time' => $end,
                    'rev_break_total' => $end->diffInMinutes($start),
                ]);
            }
        }

        return redirect()->route('attendance.update', ['attendance_id' => $attendance_id]);
        
    }

    public function submit(Request $request, $attendance_id){
        $user=Auth::user();
        $attendance=Attendance::where('user_id',$user->id);

        $revData=RevData::where('user_id',$user->id)
        ->findOrFail($attendance_id);
        $revBreak=RevBreak::where('user_id',$user->id)
        ->findOrFail($attendance_id);

        if($request->$tab==='accepted'){
            $acceptedId=Attendance::where('accepted','1')->pluck('attendance_id');
            $submittedData=Attendance::whereIn('id',$acceptedId)->get();
        }else{
            $pendingId=Attendance::where('accepted','0')->pluck('attendance_id');
            $submittedData=Attendance::whereIn('id',$pendingId)->get();
        }

        return view('request',compact('user','attendance','revData','revBreak','submittedData'));
    }
}
