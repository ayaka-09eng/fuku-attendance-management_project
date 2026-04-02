<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__title-area">
                <a href="{{ route('user.attendance.create') }}">
                    <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="coachtech_logo">
                </a>
            </div>
            @if(View::hasSection('header-button'))
            <div class="header__nav">
                @cannot('admin')
                <div class="header__nav-item">
                    <a class="header__action-link" href="{{ route('user.attendance.create') }}">勤怠</a>
                </div>
                <div class="header__nav-item">
                    <a class="header__action-link" href="{{ route('user.attendance.index') }}">勤怠一覧</a>
                </div>
                <div class="header__nav-item">
                    <a class="header__action-link" href="{{ route('request.index') }}">申請</a>
                </div>
                @endcannot

                @can('admin')
                <div class="header__nav-item">
                    <a class="header__action-link" href="{{ route('admin.attendance.index') }}">勤怠一覧</a>
                </div>
                <div class="header__nav-item">
                    <a class="header__action-link" href="{{ route('admin.staff.index') }}">スタッフ一覧</a>
                </div>
                <div class="header__nav-item">
                    <a class="header__action-link" href="{{ route('request.index') }}">申請一覧</a>
                </div>
                @endcan

                <form class="header__nav-item" action="{{ route('logout') }}" method="post">
                    @csrf
                    <button class="header__action-link" type="submit">ログアウト</button>
                </form>
            </div>
            @endif
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>

</html>