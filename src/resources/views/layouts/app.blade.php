<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH Attendance_management</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inika:wght@400;700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    @yield('css')

</head>
<body>
    <header class="header">
        <div class="header__inner">	
            <a class="header__logo" href="/attendance">
                <img src="{{asset('img/logo.svg')}}" alt="COACHTECH">
            </a>
            @if(!View::hasSection('no-nav'))
            
                @if(Auth::check())
                <div class="header-nav__buttons">
                    <a class="page-link" href="/attendance">勤怠</a>
                    <a class="page-link" href="/attendance/list">勤怠一覧</a>
                    <a class="page-link" href="/attendance/detail">申請</a>
                    <form class="logout-button" action="{{route('logout')}}" method=POST>
                        @csrf
                        <button type="submit" name="logout">ログアウト</button>
                    </form>
                </div>
                @else
                <div class="header-nav__buttons">
                    <a class="page-link" href="/attendance/list">勤怠一覧</a>
                    <a class="page-link" href="/attendance">スタッフ一覧</a>
                    <a class="page-link" href="/admin/requests">申請一覧</a>
                    <form class="logout-button" action="{{route('logout')}}" method=POST>
                        @csrf
                        <button type="submit" name="logout">ログアウト</button>
                    </form>
                </div>
                @endif
            @endif
        </div>
    </header>
    <main>
    @yield('content')
    </main>  
</body>
</html>