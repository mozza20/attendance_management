@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/application.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="title">申請一覧</h1>
    <div class="applivation-status">
        <a class="" href="">承認待ち</a>
        <a class="" href="">承認済み</a>
    </div>
</div>
@endsection