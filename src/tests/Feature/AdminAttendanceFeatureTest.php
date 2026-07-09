<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceEdit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAttendanceFeatureTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * テスト用管理者を作成
     */
    private function createAdmin(): User
    {
        return User::create([
            'name' => '管理者太郎',
            'email' => 'admin-test-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'admin' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * テスト用一般ユーザーを作成
     */
    private function createUser(): User
    {
        return User::create([
            'name' => '一般太郎',
            'email' => 'user-test-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'admin' => false,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * テスト用勤怠を作成
     */
    private function createAttendance(User $user): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-07-01',
            'clock_in_time' => '2026-07-01 09:00:00',
            'clock_out_time' => '2026-07-01 18:00:00',
            'note' => null,
        ]);
    }

    /**
     * 管理者ログインができる
     */
    public function test_admin_can_login()
    {
        $admin = $this->createAdmin();

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/attendance/list');

        $this->assertAuthenticatedAs($admin);
    }

    /**
     * 管理者勤怠一覧が表示される
     */
    public function test_admin_can_see_attendance_list()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $this->createAttendance($user);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=2026-07-01');

        $response->assertOk();

        // 修正: 一覧に一般ユーザー名と時刻が表示される
        $response->assertSee('一般太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 管理者勤怠詳細が表示される
     */
    public function test_admin_can_see_attendance_detail()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($admin)->get('/admin/attendance/' . $attendance->id);

        $response->assertOk();

        // 修正: 詳細にユーザー名・日付・時刻が表示される
        $response->assertSee('一般太郎');
        $response->assertSee('2026年');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 管理者が勤怠を修正できる
     */
    public function test_admin_can_update_attendance()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this
            ->actingAs($admin)
            ->from('/admin/attendance/' . $attendance->id)
            ->post('/admin/attendance/' . $attendance->id . '/correction', [
                // 修正: 実際のFormRequestに合わせたname
                'clock_in_time' => '10:00',
                'clock_out_time' => '19:00',
                'breaks' => [
                    [
                        'id' => '',
                        'break_in' => '12:00',
                        'break_out' => '13:00',
                    ],
                ],
                'note' => '管理者修正テスト',
            ]);

        $response->assertSessionHasNoErrors();

        $response->assertRedirect('/admin/attendance/' . $attendance->id);

        // 修正: 管理者修正は承認済みのattendance_editsとして保存される
        $this->assertDatabaseHas('attendance_edits', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_time' => '2026-07-01 10:00:00',
            'requested_clock_out_time' => '2026-07-01 19:00:00',
            'status' => '承認済み',
            'note' => '管理者修正テスト',
        ]);
    }

    /**
     * 管理者はスタッフ一覧を表示できる
     */
    public function test_admin_can_see_staff_list()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertOk();

        // 修正: 一般ユーザーがスタッフ一覧に表示される
        $response->assertSee('一般太郎');
        $response->assertSee($user->email);
    }

    /**
     * 管理者はスタッフ別勤怠一覧を表示できる
     */
    public function test_admin_can_see_staff_attendance_list()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $this->createAttendance($user);

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $user->id . '?month=2026-07');

        $response->assertOk();

        // 修正: スタッフ別勤怠一覧に日付と時刻が表示される
        $response->assertSee('一般太郎');
        $response->assertSee('07/01');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 管理者は修正申請一覧を表示できる
     */
    public function test_admin_can_see_correction_request_list()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        AttendanceEdit::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_time' => '2026-07-01 09:30:00',
            'requested_clock_out_time' => '2026-07-01 18:30:00',
            'status' => '承認待ち',
            'note' => '申請テスト',
        ]);

        $response = $this->actingAs($admin)->get('/admin/stamp_correction_request/list?status=pending');

        $response->assertOk();

        // 修正: 修正申請一覧に申請内容が表示される
        $response->assertSee('一般太郎');
        $response->assertSee('申請テスト');
        $response->assertSee('承認待ち');
    }

    /**
     * 管理者は修正申請を承認できる
     */
    public function test_admin_can_approve_correction_request()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $attendanceEdit = AttendanceEdit::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_time' => '2026-07-01 09:30:00',
            'requested_clock_out_time' => '2026-07-01 18:30:00',
            'status' => '承認待ち',
            'note' => '承認テスト',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post('/admin/stamp_correction_request/approve/' . $attendanceEdit->id);

        $response->assertRedirect('/admin/stamp_correction_request/approve/' . $attendanceEdit->id);

        $this->assertDatabaseHas('attendance_edits', [
            'id' => $attendanceEdit->id,
            'status' => '承認済み',
        ]);
    }
}