@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/lists.css') }}">
<link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="title">申請一覧</h1>

    <div class="request-status">
        <a class="pending-data {{request('tab','pending') === 'pending' ? 'active' : '' }}" href="{{route('requestLists', ['tab'=>'pending'])}}">承認待ち</a>
        <a class="pending-data {{request('tab','pending') === 'accepted' ? 'active' : '' }}" href="{{route('requestLists', ['tab'=>'accepted'])}}">承認済み</a>
    </div>

    <table class="attendance--table">
        <tr class="table--row">
            <th class="table--header">状態</th>
            <th class="table--header">名前</th>
            <th class="table--header">対象日時</th>
            <th class="table--header">申請理由</th>
            <th class="table--header">申請日時</th>
            <th class="table--header">詳細</th>
        </tr>
        @foreach($submittedData as $attendance)
            <tr class="table--row">
                <td class="table--data">{{$requestSt}}</td>
                <td class="table--data">{{$attendance->user->name}}</td>
                <td class="table--data">{{formatDate($attendance->date)}}</td>
                <td class="table--data">{{optional($attendance->revData)->remarks}}</td>
                <td class="table--data">{{formatDate($attendance->updated_at)}}</td>
                <td class="table--data">
                    <a class="detail" href="{{route('requestDetail.show',$attendance->id)}}">詳細</a>
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection