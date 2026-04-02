<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class AdminAttendanceDetailEditTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-01-15',
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'rest_start' => '12:00',
            'rest_end' => '13:00',
        ]);

        $this->actingAs($admin);

        $response = $this->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);

        $response->assertSee($user->name);
        $response->assertSee('1月15日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    public function test_出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-01-15',
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        $this->actingAs($admin);

        $response = $this->post("/admin/attendance/{$attendance->id}/fixes", [
                'clock_start' => '20:00',
                'clock_end'   => '10:00',
                'note'        => '修正',
            ]);

        $response->assertSessionHasErrors([
            'time_range' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-01-15',
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        $this->actingAs($admin);

        $response = $this->post("/admin/attendance/{$attendance->id}/fixes", [
            'clock_start' => '09:00',
            'clock_end'   => '18:00',
            'rests' => [
                0 => [
                    'rest_start' => '20:00',
                    'rest_end'   => '21:00',
                ]
            ],
            'note' => '修正',
        ]);

        $response->assertSessionHasErrors([
            'rests.0.rest_start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-01-15',
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        $this->actingAs($admin);

        $response = $this->post("/admin/attendance/{$attendance->id}/fixes", [
            'clock_start' => '09:00',
            'clock_end'   => '18:00',
            'rests' => [
                0 => [
                    'rest_start' => '12:00',
                    'rest_end'   => '20:00',
                ]
            ],
            'note' => '修正',
        ]);

        $response->assertSessionHasErrors([
            'rests.0.rest_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-01-15',
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        $this->actingAs($admin);

        $response = $this->post("/admin/attendance/{$attendance->id}/fixes", [
            'clock_start' => '09:00',
            'clock_end'   => '18:00',
            'rests' => [],
        ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}
