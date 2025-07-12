@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendanceList.css') }}">
@endsection

@section('content')
<div class="content">
    <h2 class="title">勤怠一覧</h2>
    <table class="attendance--table">
        <tr class="table--row">
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        <tr class="table--row">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                <a class="detail" href="{{route('attendanceDetail.show')}}">詳細</a>
            </td>

        </tr>
    </table>
</div>
@endsection