<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function create() {
        $userId = auth()->id();
        $attendance = Attendance::where('user_id', $userId)->whereDate('work_date', today())->first();

        if (is_null($attendance)) {
            $statusLabel = Attendance::workStatus()[0];
        } else {
            $statusLabel = $attendance->status_label;
        }

        return view('user.attendance.create', compact('statusLabel'));
    }

    public function clockIn() {
        $userId = auth()->id();
        $attendance = Attendance::where('user_id', $userId)->whereDate('work_date', today())->first();

        if (!is_null($attendance)) {
            return back()->with('error', '本日はすでに出勤済みです。');
        }

        Attendance::create([
            'user_id' => auth()->id(),
            'work_date' => today(),
            'clock_start' => now(),
            'work_status' => 1,
        ]);

        return back();
    }

    public function startRest() {
        $userId = auth()->id();
        $attendance = Attendance::where('user_id', $userId)->whereDate('work_date', today())->first();

        $attendance->rests()->create([
            'rest_start' => now(),
        ]);

        $attendance->update(['work_status' => 2]);

        return back();
    }

    public function endRest() {
        $userId = auth()->id();
        $attendance = Attendance::where('user_id', $userId)->whereDate('work_date', today())->first();

        $lastRest = $attendance->rests()->latest()->first();

        $lastRest->update([
            'rest_end' => now(),
        ]);

        $attendance->update(['work_status' => 1]);

        return back();
    }

    public function clockOut() {
        $userId = auth()->id();
        $attendance = Attendance::where('user_id', $userId)->whereDate('work_date', today())->first();

        if (is_null($attendance)) {
            return back()->with('error', '出勤記録がありません。');
        }

        $attendance->update([
            'clock_end' => now(),
            'work_status' => 3,
        ]);

        return back();
    }

    public function index(Request $request) {
        $userId = auth()->id();

        $month = $request->input('month', now()->format('Y-m'));

        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('work_date', [$start, $end])
            ->with('rests')
            ->get()
            ->keyBy(fn($a) => $a->work_date->format('Y-m-d'));

        $days = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dateKey = $current->format('Y-m-d');
            $attendance = $attendances[$dateKey] ?? null;

            $showDetail = false;
            if ($attendance && !$attendance->work_date->isToday()) {
                $showDetail = true;
            }

            $days[] = (object)[
                'date' => $current->copy(),
                'attendance' => $attendance,
                'showDetail' => $showDetail,
            ];

            $current->addDay();
        }

        return view('user.attendance.index', compact('month', 'days'));
    }

    public function show($id) {
        $attendance = Attendance::with(['user', 'rests','attendanceRequests.attendanceCorrection.restCorrections'])->findOrFail($id);

        $latestRequest = $attendance->attendanceRequests->last();

        $isPending = $latestRequest && $latestRequest->approval_status === AttendanceRequest::STATUS_PENDING;

        if ($isPending) {
            $correction = $latestRequest->attendanceCorrection;
            $clockStart = $correction->clock_start_hm;
            $clockEnd   = $correction->clock_end_hm;
            $rests      = $correction->rests;
            $note       = $latestRequest->note;
        } else {
            $clockStart = $attendance->clock_start_hm;
            $clockEnd   = $attendance->clock_end_hm;
            $rests      = $attendance->rests;
            if ($latestRequest && $latestRequest->approval_status === AttendanceRequest::STATUS_APPROVED) {
                $note = $latestRequest->note;
            } else {
                $note = null;
            }
        }

        return view('user.attendance.show', [
            'name'       => $attendance->user->name,
            'isPending'  => $isPending,
            'attendance' => $attendance,
            'clockStart' => $clockStart,
            'clockEnd'   => $clockEnd,
            'rests'      => $rests,
            'note'       => $note,
        ]);
    }
}