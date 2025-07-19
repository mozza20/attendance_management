@extends('layouts.app')

@section('content')
<div class="content">
@section('no-nav')
@endsection
    <p class="message">登録していただいたメールアドレスに認証メールを送付しました。<br>
    メール認証を完了してください。</p>
    <form class="verify-form" method="POST" action="{{ route('verification.send') }}">
        @csrf
        <a class="mail--link" href="http://localhost:8025/">認証はこちらから</a>
        <button class="send-again__button" ype="submit">認証メールを再送する</button>
    </form>
</div>

<style>
    .message{
        margin-top:240px;
        font-size:24px;
        font-weight:bold;
    }
    .verify-form{
        width:300px;
        margin:100px auto;
    }
    .mail--link{
        display:block;
        width:257px;
        height:69px;
        margin:0 auto;
        line-height:69px;
        text-align:center;
        border:1px solid #000000;
        border-radius:10px;
        background-color:#D9D9D9;
        text-decoration:none;
        color:#000000;
        font-size:24px;
        font-weight:bold;
    }
    .send-again__button{
        display:block;
        margin:70px auto;
        border:none;
        background-color:#ffffff;
        color:#0073CC;
        font-size:20px;
    }
@endsection