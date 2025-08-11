@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/lists.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendanceList.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="title">{{formatJapaneseYMD($currentDate)}}の勤怠</h1>

    <form class="select-date" action="" method="GET">
        <input type="hidden" name="ymd" value="{{ $currentDate }}">
        <button class="day-before" name="shift" value="back">前日</button>
        <p class="current-date">{{formatDate($currentDate)}}</p>
        <button class="next-day" name="shift" value="next">翌日</button>
    </form>
    
    <table class="attendance--table">
        <tr class="table--row">
            <th class=header__date>名前</th>
            <th class=header__others>出勤</th>
            <th class=header__others>退勤</th>
            <th class=header__others>休憩</th>
            <th class=header__others>合計</th>
            <th class=header__others>詳細</th>
        </tr>
        @foreach($attendances as $attendance)
        <tr class="table--row">
            <td class=data__date>{{$attendance->user->name}}</td>
            <td class="data__others">{{formatTime($attendance->start_time)}}</td>
            <td class="data__others">{{formatTime($attendance->finish_time)}}</td>
            <td class="data__others">
                {{ gmdate('G:i', $attendance->breakTimes->sum('break_total')) }}
            </td>
            <td class="data__others">{{formatTotalTime($attendance->work_total)}}</td>
            <td class="data__others">
                <a class="detail" href="{{route('attendanceDetail.show',$attendance->id)}}">詳細</a>
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection