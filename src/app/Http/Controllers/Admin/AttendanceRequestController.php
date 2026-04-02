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
        $request = AttendanceRequest::with(['user', 'attendance', 'attendanceCorrection'])->findOrFail($attendance_correct_request_id);

        return view('admin.revisions.show', compact('request'));
    }

    public function approve(Request $request, $attendance_correct_request_id) {
        DB::transaction(function () use ($request, $attendance_correct_request_id) {
            $attendanceRequest = AttendanceRequest::with('attendance.rests')->findOrFail($attendance_correct_request_id);
            $attendance = $attendanceRequest->attendance;
            $workDate = $attendance->work_date->format('Y-m-d');

            $requestedClockStart = $request->requested_clock_start ? Carbon::parse($workDate . ' ' . $request->requested_clock_start . ':00') : null;

            $requestedClockEnd = $request->requested_clock_end ? Carbon::parse($workDate . ' ' . $request->requested_clock_end . ':00') : null;

            $attendanceRequest->update([
                'approval_status' => AttendanceRequest::STATUS_APPROVED,
            ]);

            $attendance->update([
                'clock_start' => $requestedClockStart,
                'clock_end' => $requestedClockEnd,
            ]);

            $attendance->rests()->delete();

            foreach ($request->input('rest_corrections', []) as $restCorrection) {
                if (empty($restCorrection['requested_rest_start']) &&
                    empty($restCorrection['requested_rest_end'])) {
                    continue;
                }

                $requestedRestStart = $restCorrection['requested_rest_start'] ? Carbon::parse($workDate . ' ' . $restCorrection['requested_rest_start'] . ':00') : null;

                $requestedRestEnd = $restCorrection['requested_rest_end'] ? Carbon::parse($workDate . ' ' . $restCorrection['requested_rest_end'] . ':00') : null;

                Rest::create([
                    'attendance_id' => $attendance->id,
                    'rest_start' => $requestedRestStart,
                    'rest_end' => $requestedRestEnd,
                ]);
            }
        });

        return redirect()->route('admin.request.approve', $attendance_correct_request_id);
    }
}
