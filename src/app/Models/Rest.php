<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attendance;

class Rest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'rest_start',
        'rest_end',
    ];

    protected $casts = [
        'rest_start' => 'datetime',
        'rest_end' => 'datetime',
    ];

    public function attendance() {
        return $this->belongsTo(Attendance::class);
    }

    public function getDurationMinutesAttribute() {
        if (!$this->rest_start || !$this->rest_end) {
            return null;
        }

        $seconds = $this->rest_end->diffInSeconds($this->rest_start);

        if ($seconds < 60) {
            return 0;
        }

        return floor($seconds /60);
    }

    public function getRestStartHmAttribute() {
        return $this->rest_start?->format('H:i');
    }

    public function getRestEndHmAttribute() {
        return $this->rest_end?->format('H:i');
    }
}
