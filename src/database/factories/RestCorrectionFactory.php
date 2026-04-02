<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\RestCorrection;
use App\Models\AttendanceCorrection;

class RestCorrectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_correction_id' => AttendanceCorrection::factory(),
            'requested_rest_start' => now()->setTime(12, 0),
            'requested_rest_end' => now()->setTime(13, 0),
        ];
    }
}
