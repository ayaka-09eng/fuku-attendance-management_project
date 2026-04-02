<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UnifiedRequestController extends Controller
{
    public function index(Request $request) {
        if (Gate::allows('admin')) {
            return app(\App\Http\Controllers\Admin\AttendanceRequestController::class)->index($request);
        }

        return app(\App\Http\Controllers\User\AttendanceRequestController::class)->index($request);
    }
}
