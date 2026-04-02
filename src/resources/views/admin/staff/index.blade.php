@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/index.css') }}">
@endsection

@section('header-button')
@endsection

@section('content')
<div class="admin-staff-list">
    <div class="admin-staff-list__container">
        <h1 class="admin-staff-list__title">スタッフ一覧</h1>
        <div class="admin-staff-list__table">
            <table class="admin-staff-list__inner">
                <thead>
                    <tr class="admin-staff-list__row">
                        <th class="admin-staff-list__header">名前</th>
                        <th class="admin-staff-list__header">メールアドレス</th>
                        <th class="admin-staff-list__header">月次勤怠</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staffs as $staff)
                    <tr class="admin-staff-list__row">
                        <td class="admin-staff-list__cell">{{ $staff->name }}</td>
                        <td class="admin-staff-list__cell">{{ $staff->email }}</td>
                        <td class="admin-staff-list__cell">
                            <a href="{{ route('admin.staff.attendance.index', $staff->id) }}">詳細</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection