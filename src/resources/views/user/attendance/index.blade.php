@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/index.css') }}">
@endsection

@section('header-button')
@endsection

@section('content')
<div class="attendance-list">
    <div class="attendance-list__container">
        <h1 class="attendance-list__title">勤怠一覧</h1>
        @php
        $current = \Carbon\Carbon::parse($month);
        @endphp
        <div class="attendance-list__calendar-header">
            <a class="attendance-list__nav-link" href="{{ route('user.attendance.index', ['month' => $current->copy()->subMonth()->format('Y-m')]) }}">
                <span class="attendance-list__nav-arrow">&larr;</span> 前月
            </a>
            <span class="attendance-list__current-month">
                <img class="attendance-list__current-month-icon" src="{{ asset('images/カレンダーアイコン.png') }}" alt="calendar_logo">
                {{ $current->format('Y/m') }}
            </span>
            <a class="attendance-list__nav-link" href="{{ route('user.attendance.index', ['month' => $current->copy()->addMonth()->format('Y-m')]) }}">
                翌月 <span class="attendance-list__nav-arrow">&rarr;</span>
            </a>
        </div>
        <div class="attendance-list__table">
            <table class="attendance-list__inner">
                <thead>
                    <tr class="attendance-list__row">
                        <th class="attendance-list__header">日付</th>
                        <th class="attendance-list__header">出勤</th>
                        <th class="attendance-list__header">退勤</th>
                        <th class="attendance-list__header">休憩</th>
                        <th class="attendance-list__header">合計</th>
                        <th class="attendance-list__header">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($days as $day)
                    <tr class="attendance-list__row">
                        <td class="attendance-list__cell">{{ $day->date->isoFormat('MM/DD(ddd)') }}</td>
                        <td class="attendance-list__cell">{{ $day->attendance->clock_start_hm ?? '' }}</td>
                        <td class="attendance-list__cell">{{ $day->attendance->clock_end_hm ?? '' }}</td>
                        <td class="attendance-list__cell">{{ $day->attendance->break_time_hm ?? '' }}</td>
                        <td class="attendance-list__cell">{{ $day->attendance->actual_work_hm ?? '' }}</td>
                        <td class="attendance-list__cell">
                            @if ($day->showDetail)
                            <a href="{{ route('user.attendance.show', $day->attendance->id) }}">詳細</a>
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