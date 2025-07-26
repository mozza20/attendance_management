@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendanceDetail.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="title">勤怠詳細</h1>
    <form class="" action="" method="POST">
        @csrf
        <table class="attendance--table">
            <tr class="table--row">
                <th class="table--header">名前</th>
                <td class="table--data">
                    <p class="table--data__name">{{$user['name']}}</p></td>
            </tr>
            <tr class="table--row">
                <th class="table--header">日付</th>
                <td class="table--data">
                    <p class="table--data__year">{{formatJapaneseYear($attendance->date)}}</p>
                    <p class="table--data__day">{{formatJapaneseDay($attendance->date)}}</p>
                </td>
            </tr>
            <tr class="table--row">
                <th class="table--header">出勤・退勤</th>
                <td class="table--data">
                    <input class="table--data__start" type="text" pattern="[0-2][0-9]:[0-5][0-9]" value="{{formatTime($attendance->start_time)}}">
                    <p class="tilde">～</p>
                    <input class="table--data__finish" type="text" pattern="[0-2][0-9]:[0-5][0-9]" value="{{formatTime($attendance->finish_time)}}">
                </td>
            </tr>
            @foreach($breakTimes as $breakTime) 
            <tr class="table--row">
                <th class="table--header">
                    @if($loop->iteration>1)    
                        休憩{{$loop->iteration}}
                    @else
                        休憩
                    @endif
                </th>
                <td class="table--data">
                    <input class="table--data__start" type="text" pattern="[0-2][0-9]:[0-5][0-9]" value="{{formatTime($breakTime->start_time)}}">
                    <p class="tilde">～</p>
                    <input class="table--data__finish" type="text" pattern="[0-2][0-9]:[0-5][0-9]" value="{{formatTime($breakTime->end_time)}}">
                </td>
            </tr>
            @endforeach
            <tr class="table--row">
                <th class="table--header">休憩{{$breakCount+1}}</th>
                <td class="table--data">
                    <input class="table--data__start" type="text" pattern="[0-2][0-9]:[0-5][0-9]">
                    <p class="tilde">～</p>
                    <input class="table--data__finish" type="text" pattern="[0-2][0-9]:[0-5][0-9]">
                </td>
            </tr>
            <tr class="table--row">
                <th class="table--header">備考</th>
                <td class="table--data">
                    <textarea class="table--data__textarea" name="remark"></textarea>
                </td>
            </tr>
        </table>
        <div class="form--button">
            <button class="form--button__submit" type="submit">修正</button>
        </div>
    </form>
</div>
@endsection