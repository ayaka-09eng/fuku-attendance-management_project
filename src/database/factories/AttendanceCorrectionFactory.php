<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceRequest;

class AttendanceCorrectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_request_id' => AttendanceRequest::factory(),
            'requested_clock_start' => now()->setTime(9, 0),
            'requested_clock_end' => now()->setTime(18, 0),
        ];
    }
}
