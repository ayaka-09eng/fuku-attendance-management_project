<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $today = today();

        Attendance::factory()->create([
            'user_id' => $userA->id,
            'work_date' => $today,
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $userB->id,
            'work_date' => $today,
            'clock_start' => '10:00',
            'clock_end' => '19:00',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/list');

        $response->assertStatus(200);

        $response->assertSee($userA->name);
        $response->assertSee($today->format('Y/m/d'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee($userB->name);
        $response->assertSee($today->format('Y/m/d'));
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    public function test_遷移した際に現在の日付が表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $today = today()->format('Y/m/d');

        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/list');

        $response->assertStatus(200);

        $response->assertSee($today);
    }

    public function test_「前日」を押下した時に前の日の勤怠情報が表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create();

        $today = today();
        $yesterday = today()->subDay();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $yesterday,
            'clock_start' => '10:00',
            'clock_end' => '19:00',
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.attendance.index', ['date' => $today->format('Y-m-d')]));
        $response->assertStatus(200);

        $response->assertSee($today->format('Y/m/d'));

        $yesterdayUrl = route('admin.attendance.index', ['date' => $yesterday->format('Y-m-d')]);
        $responseYesterday = $this->get($yesterdayUrl);

        $responseYesterday->assertStatus(200);
        $responseYesterday->assertSee($yesterday->format('Y/m/d'));
        $responseYesterday->assertSee('10:00');
        $responseYesterday->assertSee('19:00');

        $responseYesterday->assertDontSee('09:00');
        $responseYesterday->assertDontSee('18:00');
    }

    public function test_「翌日」を押下した時に次の日の勤怠情報が表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create();

        $today = today();
        $tomorrow = today()->addDay();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $tomorrow,
            'clock_start' => '10:00',
            'clock_end' => '19:00',
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.attendance.index', ['date' => $today->format('Y-m-d')]));
        $response->assertStatus(200);

        $response->assertSee($today->format('Y/m/d'));

        $tomorrowUrl = route('admin.attendance.index', ['date' => $tomorrow->format('Y-m-d')]);
        $responseTomorrow = $this->get($tomorrowUrl);

        $responseTomorrow->assertStatus(200);
        $responseTomorrow->assertSee($tomorrow->format('Y/m/d'));
        $responseTomorrow->assertSee('10:00');
        $responseTomorrow->assertSee('19:00');

        $responseTomorrow->assertDontSee('09:00');
        $responseTomorrow->assertDontSee('18:00');
    }
}
