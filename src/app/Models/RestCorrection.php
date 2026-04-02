<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correction_id',
        'requested_rest_start',
        'requested_rest_end',
    ];

    protected $casts = [
        'requested_rest_start' => 'datetime',
        'requested_rest_end' => 'datetime',
    ];

    public function getDurationMinutesAttribute() {
        if (!$this->requested_rest_start || !$this->requested_rest_end) {
            return null;
        }

        $seconds = $this->requested_rest_end->diffInSeconds($this->requested_rest_start);

        if ($seconds < 60) {
            return 0;
        }

        return floor($seconds / 60);
    }

    public function getRestStartHmAttribute() {
        return $this->requested_rest_start?->format('H:i');
    }

    public function getRestEndHmAttribute() {
        return $this->requested_rest_end?->format('H:i');
    }
}
