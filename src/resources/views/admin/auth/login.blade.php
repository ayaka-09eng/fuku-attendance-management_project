@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/auth/login.css') }}">
@endsection

@section('content')
<div class="admin-login">
    <div class="admin-login__container">
        <h1 class="admin-login__title">管理者ログイン</h1>
        <form class="admin-login__form" action="{{ route('login') }}" method="post" novalidate>
            @csrf
            <input type="hidden" name="login_type" value="admin">
            <div class="admin-login__inner">
                <div class="admin-login__field">
                    <label class="admin-login__label" for="email">メールアドレス</label>
                    <input class="admin-login__input" type="email" id="email" name="email" value="{{ old('email') }}">
                    <div class="form__error">
                        @error('email')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="admin-login__field">
                    <label class="admin-login__label" for="password">パスワード</label>
                    <input class="admin-login__input" type="password" id="password" name="password">
                    <div class="form__error">
                        @error('password')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="admin-login__action">
                    <button class="admin-login__submit" type="submit">管理者ログインする</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection