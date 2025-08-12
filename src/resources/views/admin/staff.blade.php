@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/lists.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendanceList.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="title">スタッフ一覧</h1>

    <table class="staff--table">
        <tr class="table--row">
            <th class=header__name>名前</th>
            <th class=header__others>メールアドレス</th>
            <th class=header__others>月次勤怠</th>
        </tr>
        @foreach($users as $user)
            @if(!$user->isAdmin)
            <tr class="table--row">
                <td class=data__others>{{$user->name}}</td>
                <td class="data__others">{{$user->email}}</td>
                <td class="data__others">
                    <a class="detail" href="{{route('user.attendanceList',$user->id)}}">詳細</a>
                </td>
            </tr>
            @endif
        @endforeach        
    </table>
</div>
@endsection