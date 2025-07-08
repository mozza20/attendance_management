@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance--record">
    <p class="attendance-status">勤務外</p>
    <p class="date">{{$date}}</p>
    <p class="time">{{$time}}</p>
    <form class="form__buttons" action="" method="POST">
        @csrf
        <button class="attendance__button" name="attendance">出勤</button>
        <!-- <button class="attendance__button" name="finish">退勤</button> -->
        <!-- <button class="break__button" name="break">休憩入</button> -->
        <!-- <button class="break__button" name="break-end">休憩戻</button> -->
    </form>
</div>
@endsection