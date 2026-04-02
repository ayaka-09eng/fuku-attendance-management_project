@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/revisions/index.css') }}">
@endsection

@section('header-button')
@endsection

@section('content')
<div class="admin-request-list">
    <div class="admin-request-list__container">
        <h1 class="admin-request-list__title">申請一覧</h1>
        <ul class="admin-request-list__nav-tabs">
            <li class="admin-request-list__nav-item">
                <a class="admin-request-list__nav-link {{ $status === 'pending' ? 'active' : '' }}" href="{{ route('request.index', ['status' => 'pending']) }}">承認待ち</a>
            </li>
            <li class="admin-request-list__nav-item">
                <a class="admin-request-list__nav-link {{ $status === 'approved' ? 'active' : '' }}" href="{{ route('request.index', ['status' => 'approved']) }}">承認済み</a>
            </li>
        </ul>
        <hr class="admin-request-list__nav-underline">
        <div class="admin-request-list__table">
            <table class="admin-request-list__inner">
                <thead>
                    <tr class="admin-request-list__row">
                        <th class="admin-request-list__header">状態</th>
                        <th class="admin-request-list__header">名前</th>
                        <th class="admin-request-list__header">対象日時</th>
                        <th class="admin-request-list__header">申請理由</th>
                        <th class="admin-request-list__header">申請日時</th>
                        <th class="admin-request-list__header">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $request)
                    <tr class="admin-request-list__row">
                        <td class="admin-request-list__cell">{{ $request->status_label }}</td>
                        <td class="admin-request-list__cell">{{ $request->user->name }}</td>
                        <td class="admin-request-list__cell">{{ $request->attendance->work_date->format('Y/m/d') }}</td>
                        <td class="admin-request-list__cell">{{ $request->note }}</td>
                        <td class="admin-request-list__cell">{{ $request->created_at->format('Y/m/d') }}</td>
                        <td class="admin-request-list__cell">
                            <a href="{{ route('admin.request.approve', $request->id) }}">詳細</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection