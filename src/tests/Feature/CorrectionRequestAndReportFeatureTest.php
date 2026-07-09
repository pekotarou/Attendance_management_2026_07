<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceEdit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CorrectionRequestAndReportFeatureTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * テスト用の一般ユーザーを作成
     */
    private function createUser(string $name = 'テスト太郎'): User
    {
        return User::create([
            'name' => $name,
            'email' => 'correction-report-test-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'admin' => false,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * テスト用勤怠を作成
     */
    private function createAttendance(User $user, string $date = '2026-07-01'): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in_time' => $date . ' 09:00:00',
            'clock_out_time' => $date . ' 18:00:00',
            'note' => null,
        ]);
    }

    /**
     * 一般ユーザーは自分の承認待ち申請一覧を表示できる
     */
    public function test_user_can_see_own_pending_correction_requests()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        AttendanceEdit::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_time' => '2026-07-01 09:30:00',
            'requested_clock_out_time' => '2026-07-01 18:30:00',
            'status' => '承認待ち',
            'note' => '承認待ちテスト',
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=pending');

        $response->assertOk();

        // 修正: 承認待ち申請が表示される
        $response->assertSee('承認待ち');
        $response->assertSee('承認待ちテスト');
        $response->assertSee('テスト太郎');
    }

    /**
     * 一般ユーザーは自分の承認済み申請一覧を表示できる
     */
    public function test_user_can_see_own_approved_correction_requests()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        AttendanceEdit::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_time' => '2026-07-01 09:30:00',
            'requested_clock_out_time' => '2026-07-01 18:30:00',
            'status' => '承認済み',
            'note' => '承認済みテスト',
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=approved');

        $response->assertOk();

        // 修正: 承認済み申請が表示される
        $response->assertSee('承認済み');
        $response->assertSee('承認済みテスト');
        $response->assertSee('テスト太郎');
    }

    /**
     * 一般ユーザーの申請一覧に他人の申請は表示されない
     */
    public function test_user_cannot_see_other_users_correction_requests()
    {
        $user = $this->createUser('本人太郎');
        $otherUser = $this->createUser('他人太郎');
        $otherAttendance = $this->createAttendance($otherUser);

        AttendanceEdit::create([
            'attendance_id' => $otherAttendance->id,
            'user_id' => $otherUser->id,
            'requested_clock_in_time' => '2026-07-01 09:30:00',
            'requested_clock_out_time' => '2026-07-01 18:30:00',
            'status' => '承認待ち',
            'note' => '他人の申請',
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=pending');

        $response->assertOk();

        // 修正: 他人の申請は表示されない
        $response->assertDontSee('他人太郎');
        $response->assertDontSee('他人の申請');
    }

    /**
     * マイ勤怠レポート画面が表示できる
     */
    public function test_user_can_see_attendance_report_page()
    {
        $user = $this->createUser();

        $this->createAttendance($user, '2026-07-01');

        $response = $this->actingAs($user)
            ->get('/attendance/report');

        $response->assertOk();

        // 修正: レポート画面の見出しや基本文言が表示されることを確認
        $response->assertSee('レポート');
    }

    /**
     * マイ勤怠レポートに集計情報が表示される
     */
    public function test_attendance_report_shows_summary_data()
    {
        $user = $this->createUser();

        $this->createAttendance($user, '2026-07-01');
        $this->createAttendance($user, '2026-07-02');

        $response = $this->actingAs($user)
            ->get('/attendance/report');

        $response->assertOk();

        /// 修正: 実際のレポート画面に表示されている文言に合わせる
        $response->assertSee('総労働時間');
        $response->assertSee('総残業時間');
        $response->assertSee('平均労働時間');
        $response->assertSee('18h 0m');
        $response->assertSee('2h 0m');
    }
}
