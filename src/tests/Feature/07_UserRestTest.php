<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class UserRestTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_休憩ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => now()->subHours(2),
            'clock_end' => null,
            'work_status' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩入');

        $post = $this->post('/attendance/rest-start');
        $post->assertStatus(302);

        $this->assertDatabaseHas('rests', [
            'attendance_id' => $attendance->id,
            'rest_end' => null,
        ]);

        $after = $this->get('/attendance');
        $after->assertSee('休憩中');
    }

    public function test_休憩は一日に何回でもできる()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => now()->subHours(3),
            'clock_end' => null,
            'work_status' => 1,
        ]);

        $this->actingAs($user);

        $this->post('/attendance/rest-start');

        $this->post('/attendance/rest-end');

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        $this->assertEquals(1, Rest::where('attendance_id', $attendance->id)->count());
    }

    public function test_休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => now()->subHours(3),
            'clock_end' => null,
            'work_status' => 2,
        ]);

        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'rest_start' => now()->subMinutes(30),
            'rest_end' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        $this->post('/attendance/rest-end');

        $this->assertDatabaseMissing('rests', [
            'attendance_id' => $attendance->id,
            'rest_end' => null,
        ]);

        $after = $this->get('/attendance');
        $after->assertSee('出勤中');
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => now()->subHours(4),
            'clock_end' => null,
            'work_status' => 1,
        ]);

        $this->actingAs($user);

        $this->post('/attendance/rest-start');

        $this->post('/attendance/rest-end');

        $this->post('/attendance/rest-start');

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => now()->subHours(5),
            'clock_end' => null,
            'work_status' => 1,
        ]);

        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'rest_start' => now()->subHours(1),
            'rest_end' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $expectedBreak = $attendance->fresh()->break_time_hm;

        $response->assertSee($expectedBreak);
    }
}
