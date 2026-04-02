@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/auth/register.css') }}">
@endsection

@section('content')
<div class="registration">
    <div class="registration__container">
        <h1 class="registration__title">会員登録</h1>
        <form class="registration__form" action="" method="post" novalidate>
            @csrf
            <div class="registration__inner">
                <div class="registration__field">
                    <label class="registration__label" for="name">名前</label>
                    <input class="registration__input" type="text" id="name" name="name" value="{{ old('name') }}">
                    <div class="form__error">
                        @error('name')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="registration__field">
                    <label class="registration__label" for="email">メールアドレス</label>
                    <input class="registration__input" type="email" id="email" name="email" value="{{ old('email') }}">
                    <div class="form__error">
                        @error('email')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="registration__field">
                    <label class="registration__label" for="password">パスワード</label>
                    <input class="registration__input" type="password" id="password" name="password">
                    <div class="form__error">
                        @error('password')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="registration__field">
                    <label class="registration__label" for="password_confirmation">パスワード確認</label>
                    <input class="registration__input" type="password" id="password_confirmation" name="password_confirmation">
                </div>
                <div class="registration__action">
                    <button class="registration__submit" type="submit">登録する</button>
                </div>
            </div>
        </form>
        <div class="registration__guide">
            <a class="registration__link" href="/login">ログインはこちら</a>
        </div>
    </div>
</div>
@endsection