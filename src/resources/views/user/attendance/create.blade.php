@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/create.css') }}">
@endsection

@section('header-button')
@endsection

@section('content')
<div class="attendance-registration">
    <div class="attendance-registration__container">
        <div class="attendance-registration__status">
            <span class="status-badge">{{ $statusLabel }}</span>
        </div>
        <div class="attendance-registration__datetime">
            <p class="attendance-registration__date">{{ now()->format('Y年n月j日') }}({{ now()->translatedFormat('D') }})</p>
            <p class="attendance-registration__time">{{ now()->format('H:i') }}</p>
        </div>
        <div class="attendance-registration__actions">
            @if ($statusLabel === '勤務外')
            <form class="attendance-registration__form" action="{{ route('user.attendance.clockIn') }}" method="post">
                @csrf
                <button class="attendance-registration__action attendance-registration__action--clock-in">出勤</button>
            </form>
            @endif

            @if ($statusLabel === '出勤中')
            <form class="attendance-registration__form" action="{{ route('user.attendance.clockOut') }}" method="post">
                @csrf
                <button class="attendance-registration__action attendance-registration__action--clock-out">退勤</button>
            </form>

            <form class="attendance-registration__form" action="{{ route('user.attendance.startRest') }}" method="post">
                @csrf
                <button class="attendance-registration__action attendance-registration__action--start-rest">休憩入</button>
            </form>
            @endif

            @if ($statusLabel === '休憩中')
            <form class="attendance-registration__form" action="{{ route('user.attendance.endRest') }}" method="post">
                @csrf
                <button class="attendance-registration__action attendance-registration__action--end-rest">休憩戻</button>
            </form>
            @endif

            @if ($statusLabel === '退勤済')
            <p class="attendance-registration__message--completed">お疲れ様でした。</p>
            @endif
        </div>
    </div>
</div>
@endsection