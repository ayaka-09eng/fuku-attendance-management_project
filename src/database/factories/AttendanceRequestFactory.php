<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Attendance;

class AttendanceRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'attendance_id' => Attendance::factory(),
            'note' => $this->faker->sentence(),
            'approval_status' => 0,
        ];
    }
}
