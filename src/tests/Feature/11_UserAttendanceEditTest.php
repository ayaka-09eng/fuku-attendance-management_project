<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;

class UserAttendanceEditTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        $this->actingAs($user);

        $response = $this->post("/attendance/{$attendance->id}/fixes", [
            'requested_clock_start' => '20:00',
            'requested_clock_end' => '10:00',
            'note' => 'test',
        ]);

        $response->assertSessionHasErrors([
            'time_range' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        $this->actingAs($user);

        $response = $this->post("/attendance/{$attendance->id}/fixes", [
            'requested_clock_start' => '09:00',
            'requested_clock_end'   => '18:00',
            'rest_corrections' => [
                0 => [
                    'requested_rest_start' => '19:00',
                    'requested_rest_end'   => '19:30',
                ]
            ],
            'note' => 'test',
        ]);

        $response->assertSessionHasErrors([
            'rest_corrections.0.requested_rest_start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        $this->actingAs($user);

        $response = $this->post("/attendance/{$attendance->id}/fixes", [
            'requested_clock_start' => '09:00',
            'requested_clock_end'   => '18:00',
            'rest_corrections' => [
                0 => [
                    'requested_rest_start' => '17:00',
                    'requested_rest_end'   => '20:00',
                ]
            ],
            'note' => 'test',
        ]);

        $response->assertSessionHasErrors([
            'rest_corrections.0.requested_rest_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        $this->actingAs($user);

        $response = $this->post("/attendance/{$attendance->id}/fixes", [
            'requested_clock_start' => '09:00',
            'requested_clock_end'   => '18:00',
        ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }

    public function test_修正申請処理が実行される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        $this->actingAs($user);

        $this->post("/attendance/{$attendance->id}/fixes", [
            'requested_clock_start' => '10:00',
            'requested_clock_end'   => '19:00',
            'note' => '修正します',
            'rest_corrections' => [
                0 => [
                    'requested_rest_start' => '12:00',
                    'requested_rest_end'   => '13:00',
                ]
            ]
        ]);

        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'note' => '修正します',
            'approval_status' => 1,
        ]);

        $attendanceRequest = AttendanceRequest::where('attendance_id', $attendance->id)->first();
        $this->assertNotNull($attendanceRequest);

        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_request_id' => $attendanceRequest->id,
            'requested_clock_start' => $attendance->work_date->format('Y-m-d') . ' 10:00:00',
            'requested_clock_end'   => $attendance->work_date->format('Y-m-d') . ' 19:00:00',
        ]);

        $this->assertDatabaseHas('rest_corrections', [
            'attendance_correction_id' => $attendanceRequest->attendanceCorrection->id,
            'requested_rest_start' => $attendance->work_date->format('Y-m-d') . ' 12:00:00',
            'requested_rest_end'   => $attendance->work_date->format('Y-m-d') . ' 13:00:00',
        ]);

        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $this->actingAs($admin);

        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        $response->assertSee('修正します');

        $response->assertSee($attendance->work_date->format('Y/m/d'));

        $response = $this->get("/stamp_correction_request/approve/{$attendanceRequest->id}");
        $response->assertStatus(200);

        $response->assertSee('10:00');

        $response->assertSee('19:00');

        $response->assertSee('修正します');
    }

    public function test_「承認待ち」にログインユーザーが行った申請が全て表示されていること()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        $this->actingAs($user);

        $this->post("/attendance/{$attendance->id}/fixes", [
            'requested_clock_start' => '10:00',
            'requested_clock_end'   => '19:00',
            'note' => '修正します',
            'rest_corrections' => [
                0 => [
                    'requested_rest_start' => '12:00',
                    'requested_rest_end'   => '13:00',
                ]
            ]
        ]);

        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'note' => '修正します',
            'approval_status' => 1,
        ]);

        $response = $this->get('/stamp_correction_request/list?status=pending');
        $response->assertStatus(200);

        $response->assertSee('承認待ち');

        $response->assertSee($user->name);

        $response->assertSee($attendance->work_date->format('Y/m/d'));

        $response->assertSee('修正します');
    }

    public function test_「承認済み」に管理者が承認した修正申請が全て表示されている()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        $this->actingAs($user);

        $this->post("/attendance/{$attendance->id}/fixes", [
            'requested_clock_start' => '10:00',
            'requested_clock_end'   => '19:00',
            'note' => '修正します',
            'rest_corrections' => [
                0 => [
                    'requested_rest_start' => '12:00',
                    'requested_rest_end'   => '13:00',
                ]
            ]
        ]);

        $attendanceRequest = AttendanceRequest::first();
        $this->assertNotNull($attendanceRequest);
        $this->assertEquals(AttendanceRequest::STATUS_PENDING, $attendanceRequest->approval_status);

        $this->actingAs($admin);

        $this->post("/stamp_correction_request/approve/{$attendanceRequest->id}", [
            'requested_clock_start' => '10:00',
            'requested_clock_end'   => '19:00',
            'rest_corrections' => [
                0 => [
                    'requested_rest_start' => '12:00',
                    'requested_rest_end'   => '13:00',
                ]
            ]
        ]);

        $this->assertDatabaseHas('attendance_requests', [
            'id' => $attendanceRequest->id,
            'approval_status' => AttendanceRequest::STATUS_APPROVED,
        ]);

        $response = $this->get('/stamp_correction_request/list?status=approved');
        $response->assertStatus(200);

        $response->assertSee('承認済み');

        $response->assertSee($user->name);

        $response->assertSee($attendance->work_date->format('Y/m/d'));

        $response->assertSee('修正します');
    }

    public function test_各申請の「詳細」を押下すると勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_start' => '09:00',
            'clock_end' => '18:00',
        ]);

        $this->actingAs($user);

        $this->post("/attendance/{$attendance->id}/fixes", [
            'requested_clock_start' => '10:00',
            'requested_clock_end'   => '19:00',
            'note' => '修正します',
            'rest_corrections' => [
                0 => [
                    'requested_rest_start' => '12:00',
                    'requested_rest_end'   => '13:00',
                ]
            ]
        ]);

        $attendanceRequest = AttendanceRequest::first();
        $this->assertNotNull($attendanceRequest);

        $response = $this->get(route('request.index', ['status' => 'pending']));
        $response->assertStatus(200);

        $detailUrl = route('user.attendance.request.show', $attendanceRequest->id);

        $response->assertSee($detailUrl);

        $detailResponse = $this->get($detailUrl);

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($user->name);
        $detailResponse->assertSee($attendance->work_date->format('n月j日'));
        $detailResponse->assertSee('10:00');
        $detailResponse->assertSee('19:00');
        $detailResponse->assertSee('修正します');
    }
}
