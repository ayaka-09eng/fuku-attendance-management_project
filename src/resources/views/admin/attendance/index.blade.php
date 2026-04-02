@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/index.css') }}">
@endsection

@section('header-button')
@endsection

@section('content')
<div class="admin-attendance-list">
    <div class="admin-attendance-list__container">
        <h1 class="admin-attendance-list__title">{{ \Carbon\Carbon::parse($date)->format('Y年n月j日') }}の勤怠</h1>
        @php
        $current = \Carbon\Carbon::parse($date);
        @endphp
        <div class="admin-attendance-list__calendar-header">
            <a class="admin-attendance-list__nav-link" href="{{ route('admin.attendance.index', ['date' => $current->copy()->subDay()->format('Y-m-d')]) }}">
                <span class="admin-attendance-list__nav-arrow">&larr;</span> 前日
            </a>
            <span class="admin-attendance-list__current-month">
                <img class="admin-attendance-list__current-month-icon" src="{{ asset('images/カレンダーアイコン.png') }}" alt="calendar_logo">
                {{ $current->format('Y/m/d') }}
            </span>
            <a class="admin-attendance-list__nav-link" href="{{ route('admin.attendance.index', ['date' => $current->copy()->addDay()->format('Y-m-d')]) }}">
                翌日 <span class="admin-attendance-list__nav-arrow">&rarr;</span>
            </a>
        </div>
        <div class="admin-attendance-list__table">
            <table class="admin-attendance-list__inner">
                <thead>
                    <tr class="admin-attendance-list__row">
                        <th class="admin-attendance-list__header">名前</th>
                        <th class="admin-attendance-list__header">出勤</th>
                        <th class="admin-attendance-list__header">退勤</th>
                        <th class="admin-attendance-list__header">休憩</th>
                        <th class="admin-attendance-list__header">合計</th>
                        <th class="admin-attendance-list__header">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                    <tr class="admin-attendance-list__row">
                        <td class="admin-attendance-list__cell">{{ $attendance->user->name }}</td>
                        <td class="admin-attendance-list__cell">{{ $attendance->clock_start_hm ?? '' }}</td>
                        <td class="admin-attendance-list__cell">{{ $attendance->clock_end_hm ?? '' }}</td>
                        <td class="admin-attendance-list__cell">{{ $attendance->break_time_hm ?? '' }}</td>
                        <td class="admin-attendance-list__cell">{{ $attendance->actual_work_hm ?? '' }}</td>
                        <td>
                            @if ($attendance->showDetail)
                            <a href="{{ route('admin.attendance.show', $attendance->id) }}">詳細</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection