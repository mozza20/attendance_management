@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendanceDetail.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="title">勤怠詳細</h1>

    {{--申請済み(承認待ち)--}}
    @if($attendance->accepted === 1)
        <table class="detail--table">
            <tr class="table--row">
                <th class="table--header">名前</th>
                <td class="table--data">
                    <p class="table--data__name">{{$user['name']}}</p></td>
            </tr>
            <tr class="table--row">
                <th class="table--header">日付</th>
                <td class="table--data">
                    <p class="table--data__year">{{formatJapaneseYear($attendance->date)}}</p>
                    <p class="table--data__day">{{formatJapaneseMD($attendance->date)}}</p>
                </td>
            </tr>
            <tr class="table--row">
                <th class="table--header">出勤・退勤</th>
                <td class="table--data-p">
                    <p class="table--data__start-p">{{formatTime($revData['rev_start_time'])}}</p>
                    <p class="tilde">～</p>
                    <p class="table--data__finish-p">{{formatTime($revData['rev_finish_time'])}}</p>
                </td>
            </tr>
            @foreach($revBreaks as $index=> $revBreak) 
                <tr class="table--row">
                    <th class="table--header">
                        @if($index>0)    
                            休憩{{$index+1}}
                        @else
                            休憩
                        @endif
                    </th>
                    <td class="table--data-p">
                        <p class="table--data__start-p">{{formatTime($revBreak['rev_start_time'])}}</p>
                        <p class="tilde">～</p>
                        <p class="table--data__finish-p">{{formatTime($revBreak['rev_end_time'])}}</p>
                    </td>
                </tr>
            @endforeach
            <tr class="table--row">
                <th class="table--header">備考</th>
                <td class="table--data">{{$revData['remarks']}}</td>
            </tr>
        </table>
        <form class="form--button" action="{{route('attendanceDetail.update', $attendance_id)}}" method="POST">
            @csrf
            <button class="form--button__submit">承認</button>
        </form>

    {{--承認済み--}}
    @elseif($attendance->accepted === 2)
        <table class="detail--table">
            <tr class="table--row">
                <th class="table--header">名前</th>
                <td class="table--data">
                    <p class="table--data__name">{{$user['name']}}</p></td>
            </tr>
            <tr class="table--row">
                <th class="table--header">日付</th>
                <td class="table--data">
                    <p class="table--data__year">{{formatJapaneseYear($attendance->date)}}</p>
                    <p class="table--data__day">{{formatJapaneseMD($attendance->date)}}</p>
                </td>
            </tr>
            <tr class="table--row">
                <th class="table--header">出勤・退勤</th>
                <td class="table--data-p">
                    <p class="table--data__start-p">{{formatTime($attendance['start_time'])}}</p>
                    <p class="tilde">～</p>
                    <p class="table--data__finish-p">{{formatTime($attendance['finish_time'])}}</p>
                </td>
            </tr>
            @foreach($breakTimes as $index=> $breakTime) 
                <tr class="table--row">
                    <th class="table--header">
                        @if($index>0)    
                            休憩{{$index+1}}
                        @else
                            休憩
                        @endif
                    </th>
                    <td class="table--data-p">
                        <p class="table--data__start-p">{{formatTime($breakTime['start_time'])}}</p>
                        <p class="tilde">～</p>
                        <p class="table--data__finish-p">{{formatTime($breakTime['end_time'])}}</p>
                    </td>
                </tr>
            @endforeach
            <tr class="table--row">
                <th class="table--header">備考</th>
                <td class="table--data">{{$attendance['remarks'] ? $attendance['remarks'] : ""}}</td>
            </tr>
        </table>
        <div class="table--button">
            <button class="table--button__accepted">承認済み</button>
        </div>
    @endif
</div>
@endsection