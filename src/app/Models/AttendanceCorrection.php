<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_request_id',
        'requested_clock_start',
        'requested_clock_end',
    ];

    protected $casts = [
        'requested_clock_start' => 'datetime',
        'requested_clock_end'   => 'datetime',
    ];

    public function attendanceRequest() {
        return $this->belongsTo(AttendanceRequest::class);
    }

    public function restCorrections() {
        return $this->hasMany(RestCorrection::class);
    }

    public function rests() {
        return $this->restCorrections();
    }

    public function getTotalBreakMinutesAttribute() {
        if ($this->rests->isEmpty()) {
            return null;
        }

        return $this->rests->map(function ($rest) {
            return $rest->duration_minutes ?? 0;
        })->sum();
    }

    public function getBreakTimeHmAttribute() {
        $minutes = $this->total_break_minutes;

        if ($minutes === null) {
            return null;
        }

        $h = floor($minutes / 60);
        $m = $minutes % 60;

        return sprintf('%d:%02d', $h, $m);
    }

    public function getActualWorkMinutesAttribute() {
        if (!$this->requested_clock_start || !$this->requested_clock_end) {
            return 0;
        }
        $workMinutes = \Carbon\Carbon::parse($this->requested_clock_start)
            ->diffInMinutes(\Carbon\Carbon::parse($this->requested_clock_end));

        return $workMinutes - $this->total_break_minutes;
    }

    public function getActualWorkHmAttribute() {
        $minutes = $this->actual_work_minutes;

        if (!$minutes) {
            return null;
        }

        $h = floor($minutes / 60);
        $m = $minutes % 60;

        return sprintf('%d:%02d', $h, $m);
    }

    public function getClockStartHmAttribute() {
        return $this->requested_clock_start?->format('H:i');
    }

    public function getClockEndHmAttribute() {
        return $this->requested_clock_end?->format('H:i');
    }
}
