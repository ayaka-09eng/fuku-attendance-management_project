@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/auth/verify_email.css') }}">
@endsection

@section('content')
<div class="verify">
    <div class="verify__wrapper">
        <p class="verify__text">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>
        @env(['local', 'testing'])
        <a class="verify__link" href="http://localhost:8025/" target="_blank" rel="noopener noreferrer">認証はこちらから</a>
        @endenv
        <form class="verify__resend-form" action="{{ route('verification.send') }}" method="post">
            @csrf
            <button class="verify__resend-submit" type="submit">認証メールを再送する</button>
        </form>
    </div>
</div>
@endsection