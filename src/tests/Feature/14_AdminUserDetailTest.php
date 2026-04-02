<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class AdminUserDetailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $users = User::factory()->count(3)->create([
            'is_admin' => 0,
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/staff/list');

        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-01-10',
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'rest_start' => '12:00',
            'rest_end' => '13:00',
        ]);

        $this->actingAs($admin);

        $response = $this->get("/admin/attendance/staff/{$user->id}?month=2024-01");

        $expectedDate = \Carbon\Carbon::parse('2024-01-10')->isoFormat('MM/DD(ddd)');

        $response->assertSee($expectedDate);

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $today = '2024-02-10';
        $yesterday = '2024-02-09';

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $yesterday,
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'rest_start' => '12:00',
            'rest_end' => '13:00',
        ]);

        $expectedDate = \Carbon\Carbon::parse($yesterday)->format('Y年n月j日');

        $this->actingAs($admin);

        $this->get("/admin/attendance/list?date={$today}");

        $response = $this->get("/admin/attendance/list?date={$yesterday}");

        $response->assertStatus(200);

        $response->assertSee($expectedDate);

        $response->assertSee($user->name);

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('1:00');

        $response->assertSee('8:00');
    }

    public function test_「翌月」を押下した時に表示月の前月の情報が表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $today = '2024-02-10';
        $tomorrow = '2024-02-11';

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $tomorrow,
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'rest_start' => '12:00',
            'rest_end' => '13:00',
        ]);

        $expectedDate = \Carbon\Carbon::parse($tomorrow)->format('Y年n月j日');

        $this->actingAs($admin);

        $this->get("/admin/attendance/list?date={$today}");

        $response = $this->get("/admin/attendance/list?date={$tomorrow}");

        $response->assertStatus(200);

        $response->assertSee($expectedDate);

        $response->assertSee($user->name);

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('1:00');

        $response->assertSee('8:00');
    }

    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $date = '2024-02-10';

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $date,
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'rest_start' => '12:00',
            'rest_end' => '13:00',
        ]);

        $year = \Carbon\Carbon::parse($date)->format('Y年');
        $monthDay = \Carbon\Carbon::parse($date)->format('n月j日');

        $this->actingAs($admin);

        $this->get("/admin/attendance/list?date={$date}");

        $response = $this->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);

        $response->assertSee('勤怠詳細');

        $response->assertSee($user->name);

        $response->assertSee($year);
        $response->assertSee($monthDay);

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
