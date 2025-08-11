@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendanceDetail.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="title">勤怠詳細</h1>
    
    @if($attendance->accepted === 0) {{--未申請--}}
        <form action="{{route('attendanceDetail.confirm', ['attendance_id' => $attendance->id])}}" method="POST">
            @csrf
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
                    <td class="table--data">
                        <input class="table--data__start" type="text" name="rev_start_time" pattern="[0-2][0-9]:[0-5][0-9]" value="{{formatTime($attendance->start_time)}}">
                        <p class="tilde">～</p>
                        <input class="table--data__finish" type="text" pattern="[0-2][0-9]:[0-5][0-9]" name="rev_finish_time" value="{{formatTime($attendance->finish_time)}}">
                    </td>
                </tr>
                @foreach($breakTimes as $index=>$breakTime) 
                <tr class="table--row">
                    <th class="table--header">
                        @if($index>0)    
                            休憩{{$index+1}}
                        @else
                            休憩
                        @endif
                    </th>
                    <td class="table--data">
                        <input type="hidden" name="breaks[{{$index}}][id]" value="{{ $breakTime->id }}">
                        <input class="table--data__start" type="text" name="breaks[{{$index}}][rev_start_time]" pattern="[0-2][0-9]:[0-5][0-9]" value="{{formatTime($breakTime->start_time)}}">
                        <p class="tilde">～</p>
                        <input class="table--data__finish" type="text" name="breaks[{{$index}}][rev_end_time]" pattern="[0-2][0-9]:[0-5][0-9]" value="{{formatTime($breakTime->end_time)}}">
                    </td>
                </tr>
                @endforeach
                <tr class="table--row">
                    <th class="table--header">
                        @if($breakCount>0)    
                            休憩{{$breakCount+1}}
                        @else
                            休憩
                        @endif
                    </th>
                    <td class="table--data">
                        <input type="hidden" name="breaks[{{$breakCount}}][id]" value="">
                        <input class="table--data__start" type="text" name="breaks[{{$breakCount+1}}][rev_start_time]" pattern="[0-2][0-9]:[0-5][0-9]">
                        <p class="tilde">～</p>
                        <input class="table--data__finish" type="text" name="breaks[{{$breakCount+1}}][rev_end_time]" pattern="[0-2][0-9]:[0-5][0-9]">
                    </td>
                </tr>
                <tr class="table--row">
                    <th class="table--header">備考</th>
                    <td class="table--data">
                        <textarea class="table--data__textarea" name="remarks"></textarea>
                    </td>
                </tr>
            </table>
            <div class="form--button">
                <button class="form--button__submit" type="submit">修正</button>
            </div>
        </form>
    @elseif($attendance->accepted === 1) {{--申請済み(承認待ち)--}}
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
        <p class="status__message">*承認待ちのため修正はできません。</p>
    @endif
</div>
@endsection