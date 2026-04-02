<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'note',
        'approval_status',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function attendance() {
        return $this->belongsTo(Attendance::class);
    }

    public function attendanceCorrection() {
        return $this->hasOne(AttendanceCorrection::class);
    }

    public function getStatusLabelAttribute() {
        return match ($this->approval_status) {
            self::STATUS_PENDING => '承認待ち',
            self::STATUS_APPROVED => '承認済み',
        };
    }

    public function scopePending($query) {
        return $query->where('approval_status', self::STATUS_PENDING);
    }

    public function scopeApproved($query) {
        return $query->where('approval_status', self::STATUS_APPROVED);
    }
}
