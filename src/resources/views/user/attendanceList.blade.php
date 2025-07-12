@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendanceList.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="title">勤怠一覧</h1>    
    <div class="select-month">
       <a class="previous-month" href="">前月</a>
       <p class="current-month">2025/07</p>
       <a class="next-month" href="">翌月</a>
    </div>
    <table class="attendance--table">
        <tr class="">
            <td>日付</td>
            <td>出勤</td>
            <td>退勤</td>
            <td>休憩</td>
            <td>合計</td>
            <td>詳細</td>
        </tr>
        <tr class="">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                <a class="" href="">詳細</a>
            </td>

        </tr>
    </table>
</div>
@endsection