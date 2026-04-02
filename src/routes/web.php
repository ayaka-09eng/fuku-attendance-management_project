<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AttendanceController;
use App\Http\Controllers\User\AttendanceRequestController;
use App\Http\Controllers\UnifiedRequestController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login', function () {
    return view('user.auth.login');
})->name('login');

Route::get('/email/verify', function () {
    return view('user.auth.verify_email');
})->middleware(['auth', 'user.only'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('user.attendance.create');
})->middleware(['auth', 'user.only', 'signed'])->name('verification.verify');
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back();
})->middleware(['auth', 'user.only', 'throttle:6,1'])->name('verification.send');

Route::middleware(['auth', 'user.only', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'create'])->name('user.attendance.create');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('user.attendance.clockIn');
    Route::post('/attendance/rest-start', [AttendanceController::class, 'startRest'])->name('user.attendance.startRest');
    Route::post('/attendance/rest-end', [AttendanceController::class, 'endRest'])->name('user.attendance.endRest');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('user.attendance.clockOut');
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('user.attendance.index');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('user.attendance.show');
    Route::post('/attendance/{id}/fixes', [AttendanceRequestController::class, 'store'])->name('user.attendance.store');
    Route::get('/attendance/requests/{attendance_request}', [AttendanceRequestController::class, 'show'])->name('user.attendance.request.show');
});

Route::middleware('auth')->get(
    '/stamp_correction_request/list',
    [UnifiedRequestController::class, 'index']
)->name('request.index');