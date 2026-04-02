@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/revisions/index.css') }}">
@endsection

@section('header-button')
@endsection

@section('content')
<div class="approval-list">
    <div class="approval-list__container">
        <h1 class="approval-list__title">申請一覧</h1>
        <ul class="approval-list__nav-tabs">
            <li class="approval-list__nav-item">
                <a class="approval-list__nav-link {{ $status === 'pending' ? 'active' : '' }}" href="{{ route('request.index', ['status' => 'pending']) }}">承認待ち</a>
            </li>
            <li class="approval-list__nav-item">
                <a class="approval-list__nav-link {{ $status === 'approved' ? 'active' : '' }}" href="{{ route('request.index', ['status' => 'approved']) }}">承認済み</a>
            </li>
        </ul>
        <hr class="approval-list__nav-underline">
        <div class="approval-list__table">
            <table class="approval-list__inner">
                <thead>
                    <tr class="approval-list__row">
                        <th class="approval-list__header">状態</th>
                        <th class="approval-list__header">名前</th>
                        <th class="approval-list__header">対象日時</th>
                        <th class="approval-list__header">申請理由</th>
                        <th class="approval-list__header">申請日時</th>
                        <th class="approval-list__header">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $request)
                    <tr class="approval-list__row">
                        <td class="approval-list__cell">{{ $request->status_label }}</td>
                        <td class="approval-list__cell">{{ $user->name }}</td>
                        <td class="approval-list__cell">{{ $request->attendance->work_date->format('Y/m/d') }}</td>
                        <td class="approval-list__cell">{{ $request->note }}</td>
                        <td class="approval-list__cell">{{ $request->created_at->format('Y/m/d') }}</td>
                        <td class="approval-list__cell">
                            <a href="{{ route('user.attendance.request.show', $request->id) }}">詳細</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection