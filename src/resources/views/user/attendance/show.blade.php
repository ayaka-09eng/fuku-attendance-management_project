@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/show.css') }}">
@endsection

@section('header-button')
@endsection

@section('content')
<div class="attendance-detail">
    <div class="attendance-detail__container">
        <h1 class="attendance-detail__title">勤怠詳細</h1>
        <form class="attendance-detail__form" action="{{ route('user.attendance.store', $attendance->id) }}" method="post" novalidate>
            @csrf
            <div class="attendance-detail__inner @if($isPending) is-pending @endif">
                <div class="attendance-detail__display-field">
                    <label class="attendance-detail__label">名前</label>
                    <input class="attendance-detail__input attendance-detail__input--display" type="text" value="{{ $name }}" disabled>
                </div>
                <div class="attendance-detail__display-field">
                    <label class="attendance-detail__label">日付</label>
                    <div class="attendance-detail__inputs">
                        <input class="attendance-detail__input attendance-detail__input--display" type="text" value="{{ $attendance->work_date->format('Y年') }}" disabled>
                        <input class="attendance-detail__input attendance-detail__input--display" type="text" value="{{ $attendance->work_date->format('n月j日') }}" disabled>
                    </div>
                </div>
                <div class="attendance-detail__field-stack">
                    <div class="attendance-detail__row">
                        <label class="attendance-detail__label">出勤・退勤</label>
                        <div class="attendance-detail__inputs">
                            <input class="attendance-detail__input" type="time" name="requested_clock_start" value="{{ old('requested_clock_start', $clockStart) }}" @if ($isPending) disabled @endif>
                            <span class="attendance-detail__tilde">～</span>
                            <input class="attendance-detail__input" type="time" name="requested_clock_end" value="{{ old('requested_clock_end', $clockEnd) }}" @if($isPending) disabled @endif>
                        </div>
                    </div>
                    <div class="attendance-detail__error">
                        @error('requested_clock_start')
                        <div class="error">{{ $message }}</div>
                        @enderror

                        @error('requested_clock_end')
                        <div class="error">{{ $message }}</div>
                        @enderror

                        @error('time_range')
                        <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                @foreach ($rests as $index => $rest)
                <div class="attendance-detail__field-stack">
                    <div class="attendance-detail__row">
                        <label class="attendance-detail__label">
                            @if ($loop->first)
                            休憩
                            @else
                            休憩{{ $loop->iteration }}
                            @endif
                        </label>
                        <div class="attendance-detail__inputs">
                            <input class="attendance-detail__input" type="time" name="rest_corrections[{{ $index }}][requested_rest_start]" value="{{ old("rest_corrections.$index.requested_rest_start", $rest->rest_start_hm) }}" @if ($isPending) disabled @endif>
                            <span class="attendance-detail__tilde">～</span>
                            <input class=" attendance-detail__input" type="time" name="rest_corrections[{{ $index }}][requested_rest_end]" value="{{ old("rest_corrections.$index.requested_rest_end", $rest->rest_end_hm) }}" @if ($isPending) disabled @endif>
                        </div>
                    </div>
                    <div class="attendance-detail__error">
                        @if ($errors->has("rest_corrections.$index.requested_rest_start"))
                        <div class="error">{{ $errors->first("rest_corrections.$index.requested_rest_start") }}</div>
                        @endif

                        @if ($errors->has("rest_corrections.$index.requested_rest_end"))
                        <div class="error">{{ $errors->first("rest_corrections.$index.requested_rest_end") }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
                <div class="attendance-detail__field-stack">
                    <div class="attendance-detail__row">
                        <label class="attendance-detail__label">
                            @if ($rests->count() === 0)
                            休憩
                            @else
                            休憩{{ $rests->count() + 1 }}
                            @endif
                        </label>
                        <div class="attendance-detail__inputs">
                            <input class="attendance-detail__input" type="time" name="rest_corrections[new][requested_rest_start]" value="{{ old('rest_corrections.new.requested_rest_start') }}" @if ($isPending) disabled @endif>
                            <span class="attendance-detail__tilde">～</span>
                            <input class="attendance-detail__input" type="time" name="rest_corrections[new][requested_rest_end]" value="{{ old('rest_corrections.new.requested_rest_end') }}" @if ($isPending) disabled @endif>
                        </div>
                    </div>
                    <div class="attendance-detail__error">
                        @if ($errors->has('rest_corrections.new.requested_rest_start'))
                        <div class="error">{{ $errors->first('rest_corrections.new.requested_rest_start') }}</div>
                        @endif

                        @if ($errors->has('rest_corrections.new.requested_rest_end'))
                        <div class="error">{{ $errors->first('rest_corrections.new.requested_rest_end') }}</div>
                        @endif
                    </div>
                </div>
                <div class="attendance-detail__field-stack">
                    <div class="attendance-detail__row">
                        <label class="attendance-detail__label">備考</label>
                        <textarea class="attendance-detail__remarks" name="note" @if ($isPending) disabled @endif>{{ old('note', $note) }}</textarea>
                    </div>
                    <div class="attendance-detail__error">
                        @error('note')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
            <div class="attendance-detail__action">
                @if ($isPending)
                <p class="attendance-detail__status-message">*承認待ちのため修正はできません。</p>
                @else
                <button class="attendance-detail__submit" type="submit">修正</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection