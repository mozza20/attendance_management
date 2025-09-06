@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/lists.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendanceList.css') }}">
@endsection

@section('content') {{--スタッフ別勤怠一覧--}}
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
            <td class="data__others">{{$attendance->start_time ? formatTime($attendance->start_time) : ''}}</td>
            <td class="data__others">{{$attendance->finish_time ? formatTime($attendance->finish_time) : '' }}</td>
            @php
                $breakTotal = $attendance->breakTimes->sum('break_total');
            @endphp
            <td class="data__others">
                {{ $breakTotal > 0 ? gmdate('G:i', $breakTotal) : '' }}
            </td>
            <td class="data__others">{{$attendance->work_total ? formatTotalTime($attendance->work_total) : ''}}</td>
            <td class="data__others">
            @if($attendance->accepted === 0 || !Auth::user()->isAdmin)
                <a class="detail" href="{{route('attendanceDetail.show',$attendance->id)}}">詳細</a>
            @else
                <a class="detail" href="{{route('requestDetail.show',$attendance->id)}}">詳細</a>
            @endif
            </td>
        </tr>
        @endforeach
    </table>
    @if(Auth::user()->isAdmin)
        <form class="form--button" action="{{route('downloadCsv', $user_id)}}">
            <input type="hidden" name="ym" value="{{ $currentYM }}">
            <button class="form--button__output" type="submit">CSV出力</button>
        </form>
    @endif
</div>
@endsection