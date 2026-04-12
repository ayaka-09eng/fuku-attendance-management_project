@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/show.css') }}">
@endsection

@section('header-button')
@endsection

@section('content')
<div class="admin-attendance-detail">
    <div class="admin-attendance-detail__container">
        <h1 class="admin-attendance-detail__title">勤怠詳細</h1>
        <form class="admin-attendance-detail__form" action="{{ route('admin.attendance.store', $attendance->id) }}" method="post">
            @csrf
            <div class="admin-attendance-detail__inner @if($isPending) is-pending @endif">
                <div class="admin-attendance-detail__display-field">
                    <label class="admin-attendance-detail__label">名前</label>
                    <input class="admin-attendance-detail__input admin-attendance-detail__input--display" type="text" value="{{ $name }}" disabled>
                </div>
                <div class="admin-attendance-detail__display-field">
                    <label class="admin-attendance-detail__label">日付</label>
                    <div class="admin-attendance-detail__inputs">
                        <input class="admin-attendance-detail__input admin-attendance-detail__input--display" type="text" value="{{ $attendance->work_date->format('Y年') }}" disabled>
                        <input class="admin-attendance-detail__input admin-attendance-detail__input--display" type="text" value="{{ $attendance->work_date->format('n月j日') }}" disabled>
                    </div>
                </div>
                <div class="admin-attendance-detail__field-stack">
                    <div class="admin-attendance-detail__row">
                        <label class="admin-attendance-detail__label">出勤・退勤</label>
                        <div class="admin-attendance-detail__inputs">
                            <input class="admin-attendance-detail__input" type="time" name="clock_start" value="{{ old('clock_start', $clockStart) }}" @if ($isPending) disabled @endif>
                            <span class="admin-attendance-detail__tilde">～</span>
                            <input class="admin-attendance-detail__input" type="time" name="clock_end" value="{{ old('clock_end', $clockEnd) }}" @if ($isPending) disabled @endif>
                        </div>
                    </div>
                    <div class="admin-attendance-detail__error">
                        @error('clock_start')
                        <div class="error">{{ $message }}</div>
                        @enderror

                        @error('clock_end')
                        <div class="error">{{ $message }}</div>
                        @enderror

                        @error('time_range')
                        <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                @foreach ($rests as $index => $rest)
                <div class="admin-attendance-detail__field-stack">
                    <div class="admin-attendance-detail__row">
                        <label class="admin-attendance-detail__label">
                            @if ($loop->first)
                            休憩
                            @else
                            休憩{{ $loop->iteration }}
                            @endif
                        </label>
                        <div class="admin-attendance-detail__inputs">
                            <input class="admin-attendance-detail__input" type="time" name="rests[{{ $index }}][rest_start]" value="{{ old("rests.$index.rest_start", $rest->rest_start_hm) }}" @if ($isPending) disabled @endif>
                            <span class="admin-attendance-detail__tilde">～</span>
                            <input class="admin-attendance-detail__input" type="time" name="rests[{{ $index }}][rest_end]" value="{{ old("rests.$index.requested_rest_end", $rest->rest_end_hm) }}" @if ($isPending) disabled @endif>
                        </div>
                    </div>
                    <div class="admin-attendance-detail__error">
                        @if ($errors->has("rests.$index.rest_start"))
                        <div class="error">{{ $errors->first("rests.$index.rest_start") }}</div>
                        @endif

                        @if ($errors->has("rests.$index.rest_end"))
                        <div class="error">{{ $errors->first("rests.$index.rest_end") }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
                <div class="admin-attendance-detail__field-stack">
                    <div class="admin-attendance-detail__row">
                        <label class="admin-attendance-detail__label">
                            @if ($rests->count() === 0)
                            休憩
                            @else
                            休憩{{ $rests->count() + 1 }}
                            @endif
                        </label>
                        <div class="admin-attendance-detail__inputs">
                            <input class="admin-attendance-detail__input" type="time" name="rests[new][rest_start]" value="{{ old('rests.new.rest_start') }}" @if ($isPending) disabled @endif>
                            <span class="admin-attendance-detail__tilde">～</span>
                            <input class="admin-attendance-detail__input" type="time" name="rests[new][rest_end]" value="{{ old('rests.new.rest_end') }}" @if ($isPending) disabled @endif>
                        </div>
                    </div>
                    <div class="admin-attendance-detail__error">
                        @if ($errors->has('rests.new.rest_start'))
                        <div class="error">{{ $errors->first('rests.new.rest_start') }}</div>
                        @endif

                        @if ($errors->has('rests.new.rest_end'))
                        <div class="error">{{ $errors->first('rests.new.rest_end') }}</div>
                        @endif
                    </div>
                </div>
                <div class="admin-attendance-detail__field-stack">
                    <div class="admin-attendance-detail__row">
                        <label class="admin-attendance-detail__label">備考</label>
                        @if ($isPending)
                        <div class="admin-attendance-detail__remarks-view">{{ old('note', $note) }}</div>
                        @else
                        <textarea class="admin-attendance-detail__remarks" name="note">{{ old('note', $note) }}</textarea>
                        @endif
                    </div>
                    <div class="admin-attendance-detail__error">
                        @error('note')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
            <div class="admin-attendance-detail__action">
                @if ($isPending)
                <p class="admin-attendance-detail__status-message">*承認待ちのため修正はできません。</p>
                @else
                <button class="admin-attendance-detail__submit" type="submit">修正</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection