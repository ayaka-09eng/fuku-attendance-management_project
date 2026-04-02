<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class UserClockInTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_出勤ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤');

        $postResponse = $this->post('/attendance/clock-in', [
            'user_id' => $user->id,
        ]);

        $postResponse->assertStatus(302);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => today(),
            'work_status' => 1,
        ]);

        $responseAfter = $this->get('/attendance');
        $responseAfter->assertStatus(200);
        $responseAfter->assertSee('出勤中');
    }

    public function test_出勤は一日一回のみできる()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => now()->subHours(8),
            'clock_end' => now(),
            'work_status' => 3,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertDontSee('出勤');

        $response->assertSee('退勤済');
    }

    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $this->actingAs($user);

        $postResponse = $this->post('/attendance/clock-in', [
            'user_id' => $user->id,
        ]);

        $postResponse->assertStatus(302);

        $attendance = Attendance::where('user_id', $user->id)->first();
        $this->assertNotNull($attendance);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $expectedTime = $attendance->clock_start->format('H:i');

        $response->assertSee($expectedTime);
    }
}
