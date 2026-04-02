@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/auth/login.css') }}">
@endsection

@section('content')
<div class="login">
    <div class="login__container">
        <h1 class="login__title">ログイン</h1>
        <form class="login__form" action="/login" method="post" novalidate>
            @csrf
            <div class="login__inner">
                <div class="login__field">
                    <label class="login__label" for="email">メールアドレス</label>
                    <input class="login__input" type="email" id="email" name="email" value="{{ old('email') }}">
                    <div class="form__error">
                        @error('email')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="login__field">
                    <label class="login__label" for="password">パスワード</label>
                    <input class="login__input" type="password" id="password" name="password">
                    <div class="form__error">
                        @error('password')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="login__action">
                    <button class="login__submit" type="submit">ログインする</button>
                </div>
            </div>
        </form>
        <div class="login__guide">
            <a class="login__link" href="/register">会員登録はこちら</a>
        </div>
    </div>
</div>
@endsection