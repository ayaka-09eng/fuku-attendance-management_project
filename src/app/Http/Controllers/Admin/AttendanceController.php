<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request) {
        $date = $request->input('date', today()->format('Y-m-d'));

        $attendances = Attendance::whereDate('work_date', $date)
            ->whereHas('user', function ($query) {
                $query->where('is_admin', false);
            })
            ->with('user')
            ->orderBy('clock_start', 'desc')
            ->get()
            ->map(function ($attendance) {
                $attendance->showDetail = !$attendance->work_date->isToday();
                return $attendance;
            });

        return view('admin.attendance.index', compact('date', 'attendances'));
    }

    public function show($id) {
        $attendance = Attendance::with(['user', 'rests', 'attendanceRequests.attendanceCorrection.restCorrections'])->findOrFail($id);

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
        return view('admin.attendance.show', [
            'name'       => $attendance->user->name,
            'isPending'  => $isPending,
            'attendance' => $attendance,
            'clockStart' => $clockStart,
            'clockEnd'   => $clockEnd,
            'rests'      => $rests,
            'note'       => $note,
        ]);
    }

    public function attendanceIndex(Request $request ,$id) {
        $month = $request->input('month', now()->format('Y-m'));

        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();

        $user = User::findOrFail($id);

        $attendances = Attendance::where('user_id', $id)
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

        return view('admin.staff.attendance.index', compact('month', 'user', 'days'));
    }

    public function exportCsv(Request $request, $id) {
        $month = $request->input('month', now()->format('Y-m'));

        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$start, $end])
            ->with('rests')
            ->orderBy('work_date')
            ->get();

        $csvData = [];

        $csvData[] = ['日付', '出勤', '退勤', '休憩', '合計'];

        foreach ($attendances as $attendance) {
            $csvData[] = [
                $attendance->work_date->format('Y-m-d'),
                $attendance->clock_start_hm,
                $attendance->clock_end_hm,
                $attendance->break_time_hm,
                $attendance->actual_work_hm,
            ];
        }

        $user = User::findOrFail($id);
        $cleanName = str_replace(' ', '_', $user->name);

        $cleanName = preg_replace('/[\\\\\/:*?"<>|]/', '', $user->name);

        $filename = "勤怠データ_{$cleanName}_{$month}.csv";

        $stream = fopen('php://temp', 'r+');
        foreach ($csvData as $line) {
            fputcsv($stream, $line);
        }
        rewind($stream);

        return response()->streamDownload(function () use ($stream) {
            echo "\xEF\xBB\xBF";
            fpassthru($stream);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
