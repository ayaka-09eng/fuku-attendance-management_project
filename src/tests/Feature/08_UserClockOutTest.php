<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class UserClockOutTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_退勤ボタンが正しく機能する()
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

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤');

        $post = $this->post('/attendance/clock-out');
        $post->assertStatus(302);

        $this->assertNotNull($attendance->fresh()->clock_end);

        $after = $this->get('/attendance');
        $after->assertSee('退勤済');
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $this->actingAs($user);

        $this->post('/attendance/clock-in');

        $this->post('/attendance/clock-out');

        $attendance = Attendance::where('user_id', $user->id)->first();

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $expected = $attendance->clock_end->format('H:i');

        $response->assertSee($expected);
    }
}
