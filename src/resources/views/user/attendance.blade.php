@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance--record">
    <p class="attendance-status">{{ $attendance && $attendance->status ? $attendance->status->status : '勤務外' }}</p>
    <p class="date">{{$date}}</p>
    <p class="time">{{$time}}</p>
    <form class="form__buttons" action="{{route('attendance.input')}}" method="POST">
        @csrf
        @if($status_id===1)
            <button class="attendance__button" name="action" value="work">出勤</button>
        @elseif($status_id===2)
            <button class="attendance__button" name="action" value="leave">退勤</button>
            <button class="break__button" name="action" value="break">休憩入</button>
        @elseif($status_id===3)
            <button class="break__button" name="action" value="work">休憩戻</button>
        @elseif($status_id===4)
            <p class="finish-message">お疲れ様でした。</p>
        @endif
    </form>
</div>
@endsection