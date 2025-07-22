@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendanceList.css') }}">
@endsection

@section('content')
<div class="content">
    <h2 class="title">勤怠一覧</h2>

    <div class="select-month">
        <a href="" class="previous-month">前月</a>
        <p class="current-month">{{$thisMonth}}</p>
        <a href="" class="next-month">翌月</a>

    </div>
    <table class="attendance--table">
        <tr class="table--row">
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        @foreach($attendances as $attendance)
        <tr class="table--row">
            <td>{{formatJapaneseDate($attendance->date)}}</td>
            <td>{{formatTime($attendance->start_time)}}</td>
            <td>{{formatTime($attendance->finish_time)}}</td>
            <td>
                {{ gmdate('H:i', $attendance->breakTimes->sum('break_total')) }}
            </td>
            <td>{{formatTime($attendance->work_total)}}</td>
            <td>
                <a class="detail" href="{{route('attendanceDetail.show')}}">詳細</a>
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection