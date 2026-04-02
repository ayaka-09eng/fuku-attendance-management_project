<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Rest;
use App\Models\AttendanceRequest;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_start',
        'clock_end',
        'work_status',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_start' => 'datetime',
        'clock_end' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function rests() {
        return $this->hasMany(Rest::class);
    }

    public function attendanceRequests() {
        return $this->hasMany(AttendanceRequest::class);
    }

    public static function workStatus() {
        return [
            0 => '勤務外',
            1 => '出勤中',
            2 => '休憩中',
            3 => '退勤済',
        ];
    }

    public function getStatusLabelAttribute() {
        return self::workStatus()[$this->work_status];
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
        if (!$this->clock_start || !$this->clock_end) {
            return 0;
        }
        $workMinutes = \Carbon\Carbon::parse($this->clock_start)
            ->diffInMinutes(\Carbon\Carbon::parse($this->clock_end));

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
        return $this->clock_start?->format('H:i');
    }

    public function getClockEndHmAttribute() {
        return $this->clock_end?->format('H:i');
    }
}
