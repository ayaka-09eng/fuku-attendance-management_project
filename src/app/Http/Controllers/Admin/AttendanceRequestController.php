<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Requests\Admin\UpdateAttendanceRequest;

class AttendanceRequestController extends Controller
{
    public function index(Request $request) {
        $status = $request->query('status', 'pending');

        $requests = AttendanceRequest::with(['attendance', 'user'])
            ->whereHas('user', function ($query) {
                $query->where('is_admin', 0);
            })
            ->{$status}()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.revisions.index', compact('requests', 'status'));
    }

    public function store(UpdateAttendanceRequest $request, $id) {
        DB::transaction(function () use ($request, $id) {
            $attendance = Attendance::findOrFail($id);
            $workDate = $attendance->work_date->format('Y-m-d');

            $clockStart = $request->clock_start ? Carbon::parse($workDate . ' ' . $request->clock_start . ':00') : null;

            $clockEnd = $request->clock_end ? Carbon::parse($workDate . ' ' . $request->clock_end . ':00') : null;

            $attendance->update([
                'clock_start' => $clockStart,
                'clock_end' => $clockEnd,
            ]);

            $attendance->rests()->delete();

            foreach ($request->input('rests', []) as $rest) {
                if (empty($rest['rest_start']) ||
                    empty($rest['rest_end'])) {
                    continue;
                }

                $restStart = $rest['rest_start'] ? Carbon::parse($workDate . ' ' . $rest['rest_start'] . ':00') : null;

                $restEnd = $rest['rest_end'] ? Carbon::parse($workDate . ' ' . $rest['rest_end'] . ':00') : null;

                Rest::create([
                    'attendance_id' => $id,
                    'rest_start' => $restStart,
                    'rest_end' => $restEnd,
                ]);
            }
        });

        return redirect()->route('admin.attendance.index');
    }

    public function approveForm($attendance_correct_request_id) {
        $request = AttendanceRequest::with(['user', 'attendance', 'attendanceCorrection.restCorrections'])->findOrFail($attendance_correct_request_id);

        return view('admin.revisions.show', compact('request'));
    }

    public function approve($attendance_correct_request_id) {
        DB::transaction(function () use ($attendance_correct_request_id) {
            $attendanceRequest = AttendanceRequest::with('attendance.rests', 'attendanceCorrection.restCorrections')->findOrFail($attendance_correct_request_id);
            $attendance = $attendanceRequest->attendance;
            $attendanceCorrection = $attendanceRequest->attendanceCorrection;
            $restCorrections = $attendanceCorrection->restCorrections;

            $attendanceRequest->update([
                'approval_status' => AttendanceRequest::STATUS_APPROVED,
            ]);

            $attendance->update([
                'clock_start' => $attendanceCorrection->requested_clock_start,
                'clock_end' => $attendanceCorrection->requested_clock_end,
            ]);

            $attendance->rests()->delete();

            foreach ($restCorrections as $restCorrection) {
                $attendance->rests()->create([
                    'rest_start' => $restCorrection->requested_rest_start,
                    'rest_end' => $restCorrection->requested_rest_end,
                ]);
            }
        });

        return redirect()->route('admin.request.approve', $attendance_correct_request_id);
    }
}
