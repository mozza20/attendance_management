@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendanceDetail.css') }}">
@endsection

@section('content')
<div class="content">
    <h2 class="title">勤怠詳細</h2>
    <form class="" action="" method="POST">
        <table class="attendance--table">
            <tr class="table--row">
                <th class="table--header">名前</th>
                <td class="table--data"></td>
            </tr>
            <tr class="table--row">
                <th class="table--header">日付</th>
                <td class="table--data">
                    <p class="table--data__year"></p>
                    <p class="table--data__date"></p>
                </td>
            </tr>
            <tr class="table--row">
                <th class="table--header">出勤・退勤</th>
                <td class="table--data">
                    <p class="table--data__year"></p>
                    <p class="table--data__date"></p>
                </td>
            </tr>
            <tr class="table--row">
                <th class="table--header">出勤・退勤</th>
                <td class="table--data">
                    <input class="table--data__start" type="time"></input>
                    <input class="table--data__finish" type="time"></input>
                </td>
            </tr>
            {{--@foreach('$breaks as $break') 休憩回数を表示--}}
            <tr class="table--row">
                <th class="table--header">休憩</th>
                <td class="table--data">
                    <input class="table--data__start" type="time"></input>
                    <input class="table--data__finish" type="time"></input>
                </td>
            </tr>
            {{--@endforeach--}}
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