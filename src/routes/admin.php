<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AttendanceRequestController;

Route::get('/admin/login', function () {
    return view('admin.auth.login');
})->name('admin.login');

Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/admin/attendance/list', [AttendanceController::class, 'index'])->name('admin.attendance.index');
    Route::get('/admin/attendance/{id}', [AttendanceController::class, 'show'])->name('admin.attendance.show');
    Route::post('/admin/attendance/{id}/fixes', [AttendanceRequestController::class, 'store'])->name('admin.attendance.store');
    Route::get('/admin/staff/list', [UserController::class, 'index'])->name('admin.staff.index');
    Route::get('/admin/attendance/staff/{id}', [AttendanceController::class, 'attendanceIndex'])->name('admin.staff.attendance.index');
    Route::get('/admin/attendance/staff/{id}/csv', [AttendanceController::class, 'exportCsv'])->name('admin.staff.attendance.csv');
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AttendanceRequestController::class, 'approveForm'])->name('admin.request.approve');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AttendanceRequestController::class, 'approve'])->name('admin.request.approve.store');
});