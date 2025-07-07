@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="attendance--register">
        <p class="attendance-status">勤務外</p>
        <p class="date">2025年7月7日</p>
        <p class="time">08:00</p>
        <form class="" action="" method="POST">
            @csrf
            <button class="">出勤</button>
        </form>
    </div>

@endsection