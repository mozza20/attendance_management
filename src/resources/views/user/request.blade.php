@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="title">申請一覧</h1>
    <div class="request-status">
        <a class="pending-data {{request('tab','pending') === 'pending' ? 'active' : '' }}" href="{{route('request', ['tab'=>'pending'])}}">承認待ち</a>
        <a class="pending-data {{request('tab','pending') === 'accepted' ? 'active' : '' }}" href="{{route('request', ['tab'=>'accepted'])}}">承認済み</a>
    </div>
    <table class="request--table">
        <tr class="table--row">
            <th class="table--header">状態</th>
            <th class="table--header">名前</th>
            <th class="table--header">対象日時</th>
            <th class="table--header">申請理由</th>
            <th class="table--header">申請日時</th>
            <th class="table--header">詳細</th>
        </tr>
        <tr class="table--row">
            <td class="table--data">
                @if($accepted==="0")
                    承認待ち
                @else
                    承認済み
                @endif
            </td>
            <td class="table--data">{{$revDate->attendance_id->user_id}}</td>
            <td class="table--data">{{formatDate($attendance->date)}}</td>
            <td class="table--data">{{$revDate->remarks}}</td>
            <td class="table--data">{{formatDate($request->updated_at)}}</td>
            <td class="table--data">
                <a class="detail" href="{{route('attendanceDetail.show',$attendance->id)}}">詳細</a>
            </td>
        </tr>
    </table>
</div>
@endsection