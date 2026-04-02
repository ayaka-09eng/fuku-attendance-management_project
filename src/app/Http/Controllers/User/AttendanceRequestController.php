<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use App\Models\AttendanceCorrection;
use App\Models\RestCorrection;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceRequestController extends Controller
{
    public function index(Request $request) {
        $status = $request->query('status', 'pending');

        $user = auth()->user();

        $requests = AttendanceRequest::with('attendance')
            ->where('user_id', $user->id)
            ->whereIn('approval_status', [
                AttendanceRequest::STATUS_PENDING,
                AttendanceRequest::STATUS_APPROVED,
            ])
            ->{$status}()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.revisions.index', compact('requests', 'status', 'user'));
    }

    public function show($attendance_request) {
        $data = AttendanceRequest::with(['user', 'attendance', 'attendanceCorrection.restCorrections'])->findOrFail($attendance_request);

        $correction = $data->attendanceCorrection;

        return view('user.attendance.show', [
            'name'       => $data->user->name,
            'isPending'  => $data->approval_status === AttendanceRequest::STATUS_PENDING,
            'attendance' => $data->attendance,
            'clockStart' => $correction->clock_start_hm,
            'clockEnd'   => $correction->clock_end_hm,
            'rests'      => $correction->rests,
            'note'       => $data->note,
        ]);
    }

    public function store(UpdateAttendanceRequest $request, $id) {
        DB::transaction(function () use ($request, $id) {
            $userId = auth()->id();

            $attendance = Attendance::findOrFail($id);
            $workDate = $attendance->work_date->format('Y-m-d');

            $requestedClockStart = $request->requested_clock_start ? Carbon::parse($workDate . ' ' . $request->requested_clock_start . ':00') : null;

            $requestedClockEnd = $request->requested_clock_end ? Carbon::parse($workDate . ' ' . $request->requested_clock_end . ':00') : null;

            $attendanceRequest = AttendanceRequest::create([
                'user_id' => $userId,
                'attendance_id' => $id,
                'note' => $request->note,
                'approval_status' => AttendanceRequest::STATUS_PENDING,
            ]);

            $attendanceCorrection = AttendanceCorrection::create([
                'attendance_request_id' => $attendanceRequest->id,
                'requested_clock_start' => $requestedClockStart,
                'requested_clock_end'   => $requestedClockEnd,
            ]);

            foreach ($request->input('rest_corrections', []) as $rest) {
                if (empty($rest['requested_rest_start']) &&
                    empty($rest['requested_rest_end'])) {
                    continue;
                }

                $requestedRestStart = $rest['requested_rest_start'] ? Carbon::parse($workDate . ' ' . $rest['requested_rest_start'] . ':00') : null;

                $requestedRestEnd = $rest['requested_rest_end'] ? Carbon::parse($workDate . ' ' . $rest['requested_rest_end'] . ':00') : null;

                RestCorrection::create([
                    'attendance_correction_id' => $attendanceCorrection->id,
                    'requested_rest_start' => $requestedRestStart,
                    'requested_rest_end'   => $requestedRestEnd,
                ]);
            }
        });

        return redirect()->route('user.attendance.index');
    }
}
