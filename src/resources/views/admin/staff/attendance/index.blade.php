@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/attendance/index.css') }}">
@endsection

@section('header-button')
@endsection

@section('content')
<div class="admin-staff-attendance-list">
    <div class="admin-staff-attendance-list__container">
        <h1 class="admin-staff-attendance-list__title">{{ $user->name }}さんの勤怠</h1>
        @php
        $current = \Carbon\Carbon::parse($month);
        @endphp
        <div class="admin-staff-attendance-list__calendar-header">
            <a class="admin-staff-attendance-list__nav-link" href="{{ route('admin.staff.attendance.index', ['id' => $user->id, 'month' => $current->copy()->subMonth()->format('Y-m')]) }}">
                <span class="admin-staff-attendance-list__nav-arrow">&larr;</span> 前月
            </a>
            <span class="admin-staff-attendance-list__current-month">
                <img class="admin-staff-attendance-list__current-month-icon" src="{{ asset('images/カレンダーアイコン.png') }}" alt="calendar_logo">
                {{ $current->format('Y/m') }}
            </span>
            <a class="admin-staff-attendance-list__nav-link" href="{{ route('admin.staff.attendance.index', ['id' => $user->id, 'month' => $current->copy()->addMonth()->format('Y-m')]) }}">
                翌月 <span class="admin-staff-attendance-list__nav-arrow">&rarr;</span>
            </a>
        </div>
        <div class="admin-staff-attendance-list__table">
            <table class="admin-staff-attendance-list__inner">
                <thead>
                    <tr class="admin-staff-attendance-list__row">
                        <th class="admin-staff-attendance-list__header">日付</th>
                        <th class="admin-staff-attendance-list__header">出勤</th>
                        <th class="admin-staff-attendance-list__header">退勤</th>
                        <th class="admin-staff-attendance-list__header">休憩</th>
                        <th class="admin-staff-attendance-list__header">合計</th>
                        <th class="admin-staff-attendance-list__header">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($days as $day)
                    <tr class="admin-staff-attendance-list__row">
                        <td class="admin-staff-attendance-list__cell">{{ $day->date->isoFormat('MM/DD(ddd)') }}</td>
                        <td class="admin-staff-attendance-list__cell">{{ $day->attendance->clock_start_hm ?? '' }}</td>
                        <td class="admin-staff-attendance-list__cell">{{ $day->attendance->clock_end_hm ?? '' }}</td>
                        <td class="admin-staff-attendance-list__cell">{{ $day->attendance->break_time_hm ?? '' }}</td>
                        <td class="admin-staff-attendance-list__cell">{{ $day->attendance->actual_work_hm ?? '' }}</td>
                        <td class="admin-staff-attendance-list__cell">
                            @if ($day->showDetail)
                            <a href="{{ route('admin.attendance.show', $day->attendance->id) }}">詳細</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="admin-staff-attendance-list__action">
            <a class="admin-staff-attendance-list__submit" href="{{ route('admin.staff.attendance.csv', ['id' => $user->id, 'month' => $month]) }}">CSV出力</a>
        </div>
    </div>
</div>
@endsection