<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\AttendanceCorrection;
use App\Models\RestCorrection;

class AdminAttendanceEditTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_承認待ちの修正申請が全て表示されている()
    {
        $admin = User::factory()->create(['is_admin' => 1]);

        $user1 = User::factory()->create(['is_admin' => 0]);
        $user2 = User::factory()->create(['is_admin' => 0]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2024-02-10',
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => '2024-02-11',
        ]);

        AttendanceRequest::factory()->create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'approval_status' => 1,
            'note' => '理由1',
        ]);

        AttendanceRequest::factory()->create([
            'user_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'approval_status' => 1,
            'note' => '理由2',
        ]);

        $approvedAttendance = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2024-02-12',
        ]);

        $approved = AttendanceRequest::factory()->create([
            'user_id' => $user1->id,
            'attendance_id' => $approvedAttendance->id,
            'approval_status' => 2,
            'note' => '承認済みの理由',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);

        $response->assertSee($user1->name);
        $response->assertSee($user2->name);

        $response->assertDontSee($approved->attendance->work_date->format('Y/m/d'));
    }

    public function test_承認済みの修正申請が全て表示されている()
    {
        $admin = User::factory()->create(['is_admin' => 1]);

        AttendanceRequest::factory()->create([
            'approval_status' => 2,
            'note' => '承認済み理由A',
        ]);

        AttendanceRequest::factory()->create([
            'approval_status' => 2,
            'note' => '承認済み理由B',
        ]);

        AttendanceRequest::factory()->create([
            'approval_status' => 1,
            'note' => '承認待ち理由X',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);

        $response->assertSee('承認済み理由A');
        $response->assertSee('承認済み理由B');

        $response->assertDontSee('承認待ち理由X');
    }

    public function test_修正申請の詳細内容が正しく表示されている()
    {
        $admin = User::factory()->create(['is_admin' => 1]);

        $user = User::factory()->create(['is_admin' => 0]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-02-10',
        ]);

        $request = AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'approval_status' => 1,
            'note' => 'テスト用の申請理由',
        ]);

        $attendanceCorrection = AttendanceCorrection::factory()->create([
            'attendance_request_id' => $request->id,
            'requested_clock_start' => '2024-02-10 09:00:00',
            'requested_clock_end' => '2024-02-10 18:00:00',
        ]);

        RestCorrection::factory()->create([
            'attendance_correction_id' => $attendanceCorrection->id,
            'requested_rest_start' => '2024-02-10 12:00:00',
            'requested_rest_end' => '2024-02-10 13:00:00',
        ]);

        $this->actingAs($admin);

        $response = $this->get("/stamp_correction_request/approve/{$request->id}");

        $response->assertStatus(200);

        $response->assertSee($user->name);

        $response->assertSee('2024年');
        $response->assertSee('2月10日');

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('12:00');
        $response->assertSee('13:00');

        $response->assertSee('テスト用の申請理由');
    }

    public function test_修正申請の承認処理が正しく行われる()
    {
        $admin = User::factory()->create(['is_admin' => 1]);

        $user = User::factory()->create(['is_admin' => 0]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-02-10',
            'clock_start' => '2024-02-10 08:00:00',
            'clock_end' => '2024-02-10 17:00:00',
        ]);

        $request = AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'approval_status' => 0,
            'note' => '修正理由テスト',
        ]);

        $attendanceCorrection = AttendanceCorrection::factory()->create([
            'attendance_request_id' => $request->id,
            'requested_clock_start' => '09:00',
            'requested_clock_end' => '18:00',
        ]);

        RestCorrection::factory()->create([
            'attendance_correction_id' => $attendanceCorrection->id,
            'requested_rest_start' => '12:00',
            'requested_rest_end' => '13:00',
        ]);

        $this->actingAs($admin);

        $response = $this->post("/stamp_correction_request/approve/{$request->id}", [
            'requested_clock_start' => '09:00',
            'requested_clock_end' => '18:00',
            'rest_corrections' => [
                [
                    'requested_rest_start' => '12:00',
                    'requested_rest_end' => '13:00',
                ]
            ]
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_requests', [
            'id' => $request->id,
            'approval_status' => \App\Models\AttendanceRequest::STATUS_APPROVED,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_start' => '2024-02-10 09:00:00',
            'clock_end' => '2024-02-10 18:00:00',
        ]);

        $this->assertDatabaseHas('rests', [
            'attendance_id' => $attendance->id,
            'rest_start' => '2024-02-10 12:00:00',
            'rest_end' => '2024-02-10 13:00:00',
        ]);
    }
}
