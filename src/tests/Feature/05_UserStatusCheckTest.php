<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class UserStatusCheckTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_勤務外の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('勤務外');
    }

    public function test_出勤中の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => now(),
            'clock_end' => null,
            'work_status' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('出勤中');
    }

    public function test_休憩中の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => now(),
            'clock_end' => null,
            'work_status' => 2,
        ]);

        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'rest_start' => now()->subMinutes(10),
            'rest_end' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('休憩中');
    }

    public function test_退勤済の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => now(),
            'work_status' => 3,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('退勤済');
    }
}
