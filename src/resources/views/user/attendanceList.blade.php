@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/lists.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendanceList.css') }}">
@endsection

@section('content')
<div class="content">
    @if(Auth::user()->isAdmin)
        <h1 class="title">{{$user->name}}さんの勤怠</h1>
    @else
        <h1 class="title">勤怠一覧</h1>
    @endif

    <form class="select-month" action="" method="GET">
        <input type="hidden" name="ym" value="{{ $currentYM }}">
        <button class="previous-month" name="shift" value="back">前月</button>
        <p class="current-month">{{$thisMonth}}</p>
        <button class="next-month" name="shift" value="next">翌月</button>
    </form>
    
    <table class="attendance--table">
        <tr class="table--row">
            <th class=header__date>日付</th>
            <th class=header__others>出勤</th>
            <th class=header__others>退勤</th>
            <th class=header__others>休憩</th>
            <th class=header__others>合計</th>
            <th class=header__others>詳細</th>
        </tr>
        @foreach($attendances as $attendance)
        <tr class="table--row">
            <td class=data__date>{{formatJapaneseDate($attendance->date)}}</td>
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
    @if(Auth::user()->isAdmin)
        <form class="form--button" action="{{route('downloadCsv', $user_id)}}">
            <button class="form--button__output" type="submit">CSV出力</button>
        </form>
    @endif
</div>
@endsection