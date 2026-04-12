@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/revisions/show.css') }}">
@endsection

@section('header-button')
@endsection

@section('content')
<div class="admin-request-approval">
    <div class="admin-request-approval__container">
        <h1 class="admin-request-approval__title">勤怠詳細</h1>
        <div class="admin-request-approval__inner">
            <div class="admin-request-approval__display-field">
                <label class="admin-request-approval__label">名前</label>
                <input class="admin-request-approval__input" type="text" value="{{ $request->user->name }}" disabled>
            </div>
            <div class="admin-request-approval__display-field">
                <label class="admin-request-approval__label">日付</label>
                <div class="admin-request-approval__inputs">
                    <input class="admin-request-approval__input" type="text" value="{{ $request->attendance->work_date->format('Y年') }}" disabled>
                    <input class="admin-request-approval__input" type="text" value="{{ $request->attendance->work_date->format('n月j日') }}" disabled>
                </div>
            </div>
            <div class="admin-request-approval__field">
                <label class="admin-request-approval__label">出勤・退勤</label>
                <div class="admin-request-approval__inputs">
                    <input class="admin-request-approval__input" type="time" name="requested_clock_start" value="{{ old('requested_clock_start', $request->attendanceCorrection->clock_start_hm) }}" readonly>
                    <span class="admin-request-approval__tilde">～</span>
                    <input class="admin-request-approval__input" type="time" name="requested_clock_end" value="{{ old('requested_clock_end', $request->attendanceCorrection->clock_end_hm) }}" readonly>
                </div>
            </div>
            @foreach ($request->attendanceCorrection->restCorrections as $index => $rest)
            <div class="admin-request-approval__field">
                <label class="admin-request-approval__label">
                    @if ($loop->first)
                    休憩
                    @else
                    休憩{{ $loop->iteration }}
                    @endif
                </label>
                <div class="admin-request-approval__inputs">
                    <input class="admin-request-approval__input" type="time" name="rest_corrections[{{ $index }}][requested_rest_start]" value="{{ old("rest_corrections.$index.requested_rest_start", $rest->rest_start_hm) }}" readonly>
                    <span class="admin-request-approval__tilde">～</span>
                    <input class="admin-request-approval__input" type="time" name="rest_corrections[{{ $index }}][requested_rest_end]" value="{{ old("rest_corrections.$index.requested_rest_end", $rest->rest_end_hm) }}" readonly>
                </div>
            </div>
            @endforeach
            <div class="admin-request-approval__field">
                <label class="admin-request-approval__label">
                    @if ($request->attendanceCorrection->restCorrections->count() === 0)
                    休憩
                    @else
                    休憩{{ $request->attendanceCorrection->restCorrections->count() + 1 }}
                    @endif
                </label>
                <div class="admin-request-approval__inputs">
                    <input class="admin-request-approval__input" type="time" name="rest_corrections[new][requested_rest_start]" value="{{ old('rest_corrections.new.requested_rest_start') }}" readonly>
                    <span class="admin-request-approval__tilde">～</span>
                    <input class="admin-request-approval__input" type="time" name="rest_corrections[new][requested_rest_end]" value="{{ old('rest_corrections.new.requested_rest_end') }}" readonly>
                </div>
            </div>
            <div class="admin-request-approval__field">
                <label class="admin-request-approval__label">備考</label>
                <div class="admin-request-approval__remarks">{{ old('note', $request->note) }}</div>
            </div>
        </div>
        <div class="admin-request-approval__action">
            @if ($request->approval_status === \App\Models\AttendanceRequest::STATUS_APPROVED)
            <span class="admin-request-approval__submit admin-request-approval__submit--approved">承認済み</span>
            @else
            <form class="admin-request-approval__form" action="{{ route('admin.request.approve.store', $request->id) }}" method="post">
                @csrf
                <button class="admin-request-approval__submit" type="submit">承認</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection