<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class UserAttendanceListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_自分が行った勤怠情報が全て表示されている()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendances = collect();

        for ($i = 1; $i <= 3; $i++) {
            $attendances->push(
                Attendance::factory()->create([
                    'user_id' => $user->id,
                    'work_date' => now()->startOfMonth()->addDays($i),
                    'clock_start' => now()->setTime(9, 0),
                    'clock_end' => now()->setTime(18, 0),
                    'work_status' => 3,
                ])
            );
        }

        Attendance::factory()->create([
            'user_id' => User::factory()->create()->id,
            'work_date' => now()->startOfMonth()->addDays(1),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->clock_start_hm);
            $response->assertSee($attendance->clock_end_hm);
        }
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $expected = now()->format('Y/m');
        $response->assertSee($expected);
    }

    public function test_「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $lastMonth = now()->subMonth()->format('Y-m');

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->subMonth()->startOfMonth(),
            'clock_start' => now()->subMonth()->startOfMonth()->setTime(9, 0),
            'clock_end' => now()->subMonth()->startOfMonth()->setTime(18, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list?month=' . $lastMonth);
        $response->assertStatus(200);

        $response->assertSee($attendance->clock_start_hm);
        $response->assertSee($attendance->clock_end_hm);
    }

    public function test_「翌月」を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $nextMonth = now()->addMonth()->format('Y-m');

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->addMonth()->startOfMonth(),
            'clock_start' => now()->addMonth()->startOfMonth()->setTime(9, 0),
            'clock_end' => now()->addMonth()->startOfMonth()->setTime(18, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list?month=' . $nextMonth);
        $response->assertStatus(200);

        $response->assertSee($attendance->clock_start_hm);
        $response->assertSee($attendance->clock_end_hm);
    }

    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
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

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $detailUrl = '/attendance/detail/' . $attendance->id;

        $detail = $this->get($detailUrl);
        $detail->assertStatus(200);

        $detail->assertSee($attendance->clock_start_hm);
        $detail->assertSee($attendance->clock_end_hm);
    }
}
