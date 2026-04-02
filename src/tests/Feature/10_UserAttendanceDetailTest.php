<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class UserAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => now()->setTime(9, 0),
            'clock_end' => now()->setTime(18, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        $response->assertSee('山田太郎');
    }

    public function test_勤怠詳細画面の「日付」が選択した日付になっている()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => now()->setTime(9, 0),
            'clock_end' => now()->setTime(18, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        $expectedDate = $attendance->work_date->format('n月j日');

        $response->assertSee($expectedDate);
    }

    public function test_「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => now()->setTime(9, 0),
            'clock_end' => now()->setTime(18, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        $response->assertSee($attendance->clock_start_hm);
        $response->assertSee($attendance->clock_end_hm);
    }

    public function test_「休憩」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => now()->setTime(9, 0),
            'clock_end' => now()->setTime(18, 0),
        ]);

        $rest = Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'rest_start' => now()->setTime(12, 0),
            'rest_end' => now()->setTime(13, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        $response->assertSee($rest->rest_start->format('H:i'));
        $response->assertSee($rest->rest_end->format('H:i'));
    }
}
